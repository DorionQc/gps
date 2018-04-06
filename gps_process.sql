/* Geremy Desmanche 
   Procedures, functions and triggers for automagically managing gps database. 
   Sun 11 Feb 2018 02:49:40 PM EST
*/

Use gps;

DELIMITER //

/* Utilities */

Create Or Replace Function PackagingAmountFor(itemID int(10) unsigned, amount int(10) unsigned) 
Returns int(10) unsigned 
READS SQL DATA
BEGIN
    return (Select CEILING(amount / amountPerPackaging) From items Where ID=itemID);
END //

/* Internal-use only procedures often bypassing checks. */

Create or Replace Procedure AddCommandToVehicleOnStandBy(commandID int(10) unsigned, vehicleID int(10) unsigned)
BEGIN
    Declare iamount TYPE OF vehicle_items.amount;
    Declare lastUsedByItem TYPE OF vehicles.usedCapacity;
    Declare nextUsedByItem TYPE OF vehicles.usedCapacity;
    Declare currentFilling TYPE OF vehicles.usedCapacity;
    Declare vcapacity TYPE OF vehicles.capacity;
    Declare item ROW TYPE OF items;

    Declare done tinyint(1) Default 0;
    Declare items Cursor For 
        Select itemID, amount From command_items ci
            Where command_items.commandId = commandID;
    Declare CONTINUE HANDLER For NOT FOUND Set done = 1;
    
    /* Fetching few useful data. */
    Select usedCapacity, capacity Into currentFilling, vcapacity 
        From vehicles Where ID = vehicles.ID;
    
    Open items;
    loupe: Loop 
        Fetch items into item;
        If done = 1 Then
            leave loupe;
        End If;
        
        Select MAX(amount) Into iamount From vehicle_items
            Where itemID = item.itemID and vehicleID = vehicle;
        
        /* If the item can be found within the vehicle. */
        If iamount is not null THEN
            Set lastUsedByItem = PackagingAmountFor(item.itemID, iamount);
            Set nextUsedByItem = PackagingAmountFor(item.itemID, iamount + item.amount);
            
            If nextUsedByItem > lastUsedByItem Then 
                Set currentFilling = currentFilling + (nextUsedByItem - lastUsedByItem);
            End if;
            
            Update vehicle_items Set amount = amount + itemID.amount 
                Where itemID = item.itemID and vehicleID = vehicle;
        else
            Set currentFilling = currentFilling + PackagingAmountFor(item.itemID, item.amount);
            Insert into vehicle_items (vehicleID, itemID, amount) 
                values (vehicle, item.itemID, item.amount);
        end if;
    End Loop;
    Close items;
    
    
        
    Update vehicle set usedCapacity = currentFilling where ID = vehicle;
END //

/* Fonctions for verifications. Quick stuff returning code. */

/*
  Returns:
  0 - Not within.
  1 - Within.
*/
Create Or Replace Function AreCoupled(commandID int(10) unsigned, sessionID int(10) unsigned) 
returns tinyint(1)
READS SQL DATA 
BEGIN
    return (Select COUNT(ID) From commands c
            Where c.sessionID = sessionID and c.ID = commandID);
END //


/*
  Returns:
  1 - Vehicle is currently on the run.
  0 - Vehicle is not on the run right now.
*/
Create Or Replace Function IsOnTheRoad(vehicleID int(10) unsigned)
Returns tinyint(1)
READS SQL DATA
BEGIN
    Declare r tinyint(1);

    Select COUNT(sessions.ID) into r From sessions 
        where sessions.vehicleID = vehicleID
       and sessions.start is not null and sessions.end is null;
    return r;
END //


/*
  Returns:
  0 - Session has yet to start.
  1 - Session is completed or in the middle of completion.
*/
Create Or Replace Function SessionStatus(sessionID int(10) unsigned)
Returns tinyint(1)
READS SQL DATA
BEGIN
    return (Select If(start is null, 0, 1) From sessions 
        Where ID = sessionID);
END //

/*
  Returns:
  1 - One or more item in the command needs conditioning.
  0 - No item need conditioning within the command.
*/
Create Or Replace Function NeedsConditioning(commandID int(10) unsigned)
Returns tinyint(1)
READS SQL DATA
BEGIN
    Declare r tinyint(1);
    Select If(count(items.id) > 0, 1, 0) into r from items 
        Join command_items On command_items.itemID = items.ID
    Where command_items.commandID = commandID and items.conditioning = 1;
    return r;
END //


/*
  Returns:
  1 - Command is within a session that's on the run or finished.
  0 - Command has is in an inactive session or no session yet.
*/
Create Or Replace Function Completeness(commandID int(10) unsigned)
Returns tinyint(1)
READS SQL DATA
BEGIN
    Declare r tinyint(1);

    Select COUNT(commands.ID) into r From commands
        Join sessions On sessions.ID = commands.sessionID
    Where commands.ID = commandID and sessions.start is null;
    return r;
END //

/*
  Returns:
  0 - Vehicle is available and has enough space left.
  2 - Vehicle is available but doesn't have enough space left.
  1 - Vehicle is on the road but already has needed items.
  3 - Vehicle is on the road and doesn't have needed items.
  4 - Vehicle doesn't have needed equipment for selected items.
*/
Create or Replace Function CanContain(commandID int(10) unsigned, vehicleID int(10) unsigned)
Returns tinyint(1)
READS SQL DATA
BEGIN
    Declare conditionz TYPE OF vehicles.conditioning;
    Declare vamount TYPE OF vehicle_items.amount;
    Declare vtrueAmount TYPE OF vehicle_items.trueAmount;
    Declare lastUsedByItem TYPE OF vehicles.usedCapacity;
    Declare nextUsedByItem TYPE OF vehicles.usedCapacity;
    Declare currentFilling TYPE OF vehicles.usedCapacity;
    Declare vcapacity TYPE OF vehicles.capacity;
    Declare item ROW TYPE OF command_items;

    Declare done tinyint(1) Default 0;
    Declare itemsc Cursor For 
        Select * From command_items 
            Where command_items.commandId = commandID;
    Declare CONTINUE HANDLER For NOT FOUND Set done = 1;
    
    /* If vehicle is on the road, only check current content. */
    If IsOnTheRoad(vehicleID) = 1 Then 
        Open itemsc; 
        loupe: Loop
            Fetch itemsc Into item;
            If done Then
                Leave loupe;
            End if;
            
            Select amount, trueAmount Into vamount, vtrueAmount
                From vehicle_items Where itemID = item.itemID;
                
            Set vamount = vtrueAmount - vamount;
            If vamount < item.amount Then 
                return 3;
            End If;
        End Loop;
        Close itemsc;
        return 1;
    End If;
    

    /* Fetching few useful data. */
    Select usedCapacity, capacity Into currentFilling, vcapacity 
        From vehicles Where ID = vehicleID;
    
    /* If vehicle is on standby, see if and how it can be inserted the items. */
    
    Select conditioning Into conditionz From vehicles Where ID = vehicleID;
    If conditionz < NeedsConditioning(commandID) Then 
        return 4;
    End If;
    
    Open itemsc;
    loupe: Loop 
        Fetch itemsc into item;
        If done = 1 Then
            leave loupe;
        End If;
        
        Select MAX(amount) Into vamount From vehicle_items
            Where itemID = item.itemID and vehicleID = vehicle_items.vehicleID;
        /* If the item can't be found within the vehicle. */
        if vamount is not null THEN
            Set lastUsedByItem = PackagingAmountFor(item.itemID, vamount);
            Set nextUsedByItem = PackagingAmountFor(item.itemID, vamount + item.amount);
            
            If nextUsedByItem > lastUsedByItem Then 
                Set currentFilling = currentFilling + (nextUsedByItem - lastUsedByItem);
            End if;
        else
            Set currentFilling = currentFilling + PackagingAmountFor(item.itemID, item.amount);
        end if;
    End Loop;
    Close itemsc;
    
    If currentFilling > vcapacity THEN
        return 2;
    End If;
    return 0;
END //

/*
  Returns:
  0 - Driver and Vehicle can be combined right now.
  1 - Driver's licence doesn't allow him to drive selected vehicle.
  2 - Selected vehicle isn't available right now.
  3 - Selected driver isn't available right now.
*/
Create Or Replace Function CanBeCombined(driverID int(10) unsigned, vehicleID int(10) unsigned)
Returns tinyint(1)
READS SQL DATA
BEGIN
    Declare r tinyint(1);
    Declare dlicence TYPE OF drivers.licence;
    Declare vehicle TYPE OF vehicles.ID;
    Declare driver TYPE OF drivers.ID;

    Declare done int Default False;
    Declare reserved_vehicles Cursor For 
        Select sessions.vehicleID From sessions Where sessions.end is null And session.start is not null;
    Declare reserved_drivers Cursor For
        Select sessions.driverID From sessions Where sessions.end is null;
    Declare CONTINUE HANDLER For NOT FOUND SET done = true;

    Select licence Into dlicence From drivers
        Where ID = driverID;
    Select COUNT(ID) Into r From vehicles where licence >= dlicence And Id = VehicleID;
    If r = 0 Then 
        return 1;
    End if;

    Open reserved_vehicles;
    loupe: Loop
        Fetch reserved_vehicles Into vehicle;
        If done Then
            Leave loupe;
        End If;
    
        If vehicle = vehicleID Then
            return 2;
        end if;
    End loop;
    Close reserved_vehicles;

    Set done = false;

    Open reserved_drivers;
    otre_loupe: Loop
        Fetch reserved_drivers Into driver;
        If done Then
            Leave otre_loupe;
        End if;

        If driver = driverID Then
            return 3;
        End If;
    End loop;
    return 0;
END //


/* Functions for easy data modification with low risk against data integrity.*/

/*
  Returns:
  0 - Notification has successfully been added.
*/
Create or Replace Function AddNotify(sessionID int(10) unsigned)
returns tinyint(1)
DETERMINISTIC MODIFIES SQL DATA
BEGIN 
    Insert Into session_messages Values (sessionID);
    return 0;
END // 

/*
  Returns:
  0 - No notification for selected session.
  1 - Selected session has a notification.
*/
Create or Replace Function CheckNotify(sessionID int(10) unsigned)
returns tinyint(1)
READS SQL DATA
BEGIN 
    return (Select COUNT(sm.sessionID) From session_messages sm 
        Where sm.sessionID = sessionID); 
END //

/*
  Returns:
  0 - Specified amount of selected item was added to targeted command.
  1 - Cannot modify command in the midst of delivery.
*/
Create or Replace function AddCommandItem (commandID int(10) unsigned, itemID int(10) unsigned, amount int(10) unsigned)
Returns tinyint(1)
DETERMINISTIC MODIFIES SQL DATA
BEGIN
    DECLARE conditionz tinyint(1);
    DECLARE newAmount TYPE OF command_items.amount;

    If Completeness(commandID) = 1 Then
        return 1;
    End If;
    
    Select COUNT(ci.itemID) Into conditionz From command_items ci
        Where ci.commandID = commandID and ci.itemID = itemID;
    If conditionz = 0 Then
        Insert into command_items (itemID, commandID, amount) 
            Values (itemID, commandID, amount);
    Else 
        Select ci.amount Into newAmount From command_Items ci
            Where ci.itemID = itemID and ci.commandID = commandID;
        Set newAmount = amount + newAmount;
        Update command_items ci Set ci.amount = amount
            Where ci.commandID = commandID 
        And ci.itemID = itemID;
    End If;
    return 0;
END // 


/*
  Returns:
  0 - Command is not linked to any session.
  1 - Command has been found within a session which is on the road.
*/
Create Or Replace Function RemoveCommandSession(commandID int(10) unsigned)
RETURNS tinyint(1)
DETERMINISTIC MODIFIES SQL DATA
BEGIN
    DECLARE session TYPE OF commands.sessionID;
    DECLARE conditionz tinyint(1);

    Select sessionID Into session From commands Where ID = commandID;
    If session is not null Then
    Set conditionz = SessionStatus(session);
        If conditionz = 1 Then
            Update commands Set sessionID = null Where ID = commandID;
            return 0;
        Else
            return 1;    
        End If;
    End If;
    return 0;
END //

/*
  Returns:
  0 - Command was successfully added to targeted session.
  1 - Command has been found within a session which is on the road.
  2 - Target session is already on the road and doesn't contain enough of selected items.
  3 - Command is too large for space left within selected session vehicle.
*/
Create Or Replace Function AddCommandToSession (commandID int(10) unsigned, sessionID int(10) unsigned)
RETURNS tinyint(1)
DETERMINISTIC MODIFIES SQL DATA
BEGIN
    Declare conditionz tinyint(1);
    Declare item Row TYPE OF items;
    Declare vamount TYPE OF vehicle_items.amount;
    Declare vtrueAmount TYPE OF vehicle_items.trueAmount;
    Declare vehicle TYPE OF vehicle_items.vehicleID;

    Declare done int Default False;
    Declare items Cursor For 
        Select itemID, amount From command_items ci
            Where command_items.commandId = commandID;
    Declare CONTINUE HANDLER For NOT FOUND SET done = true;
    
    Set conditionz = RemoveCommandSession(commandID);
    If conditionz != 0 Then 
        return 1;
    End If;

    /* Get vehicle to be filled. */
    Select vehicleID Into vehicle From sessions
        Where ID = sessionID;
        
    /* Check if the vehicle and the command are compatible right now. */
    Set conditionz = CanContain(commandID, vehicle); 
    If conditionz < 2 Then
        Call AddCommandToVehicleOnStandBy(commandID, vehicle);
        return 0;
    ELSE
        If conditionz = 2 Then 
            return 3;
        Else 
            return 2;
        End If;
    End If;
END //

/*
  Returns:
  0 - Command has been delivered.
  1 - Selected session doesn't have access to selected command.
  2 - Selected session is not live.
  3 - Selected command was already complete.
*/
Create or Replace Function CompleteDelivery(sessionID int(10) unsigned, commandID int(10) unsigned)
returns tinyint(1)
DETERMINISTIC MODIFIES SQL DATA
BEGIN 
    Declare conditionz tinyint(1);
    Declare vehicle TYPE OF sessions.vehicleID;
    Declare item Row (itemid int, amount int, cost double, supplierid int);

    Declare done int Default False;
    Declare items Cursor For 
        Select itemID, amount, cost, supplierID From command_items ci
            Join items On items.Id = itemId
            Where ci.commandId = commandID;
    Declare CONTINUE HANDLER For NOT FOUND SET done = true;
    
    
    /* Few obvious checks. */
    Set conditionz = AreCoupled(commandID, sessionID);
    If conditionz = 0 Then
        return 1;
    End If;
    
    Set conditionz = SessionStatus(sessionID);
    If conditionz = 0 Then
        return 2;
    End if;
    
    Select complete Into conditionz From commands Where ID = commandID;
    If conditionz = 1 THEN
        return 3;
    End If;
    
    /* Select useful data. */
    Select vehicleID into vehicle From sessions 
        Where sessions.ID = sessionID;
    
    /* Complete the command. */
    Open items;
    loupe: Loop 
        Fetch items Into item;
        If done = 1 Then 
            leave loupe;
        End If;
        
        Update vehicle_items 
            Set amount = amount - item.amount,
                trueAmount = trueAmount - item.amount
            Where itemID = item.itemID and vehicleID = vehicle;
        
        Update suppliers Set bill = bill + (item.amount * item.cost)
            Where Id = item.supplierId;
    
    End Loop;
    Close items;

    Update commands set complete=1 where id=commandId;
    
    return 0;
END //

/*
  Returns:
  0 - Session has been started.
  1 - Session state didn't allow a fresh start.
  2 - No driver was found for this session.
*/
Create or Replace Function StartSession(sessionID int(10) unsigned, startTime timestamp)
Returns tinyint(1)
DETERMINISTIC MODIFIES SQL DATA
BEGIN
    Declare conditionz tinyint(1);
    Declare driver TYPE OF sessions.driverID;
    Declare vehicle TYPE OF vehicle_items.vehicleId;
    Declare item ROW (itemId int, amount int, amountPerPackaging int);
    
    Declare done int Default False;
    Declare items Cursor For 
        Select itemId, amount, amountPerPackaging From vehicle_items
            Join items On items.Id = itemId
            Where vehicleId = vehicle;
    Declare CONTINUE HANDLER For NOT FOUND SET done = true;

    Set conditionz = sessionStatus(sessionID);
    If conditionz != 0 Then
        return 1;
    End If;
    
    Select sessions.driverID Into driver From sessions 
        Where sessions.ID = sessionID;
    If driver is null Then
        return 2;
    End If;

    /* Get useful data. */
    Select vehicleId Into vehicle From sessions Where Id = sessionId;
    
    /* Complete the filling of concerned vehicle. */
    Open items;
    loupe: Loop 
        Fetch items Into item;
        If done = 1 Then 
            leave loupe;
        End If;
        
        Update vehicle_items 
            Set trueAmount = PackagingAmountFor(item.itemID, item.amount) 
                           * item.amountPerPackaging
            Where itemID = item.itemID and vehicleID = vehicle;
    End Loop;
    Close items;
    
    Update sessions Set sessions.start = startTime 
        Where ID = sessionID;

    return 0;
END //

/*
  Returns:
  0 - Session has been ended.
  1 - Session state didn't allow it to end.
  2 - Can't terminate session if there are still commands within it.
*/
Create or Replace Function EndSession(sessionID int(10) unsigned, endTime timestamp)
Returns tinyint(1)
DETERMINISTIC MODIFIES SQL DATA
BEGIN
    Declare conditionz tinyint(1);
    Declare vehicle TYPE OF sessions.vehicleID;
    Declare itemAmount int(10) unsigned;
    
    Set conditionz = sessionStatus(sessionID);
    If conditionz != 1 Then 
        return 1;
    End If;
    
    /* Select useful data. */
    Select vehicleID into vehicle From sessions 
        Where sessions.ID = sessionID;
    
    Select SUM(amount) Into itemAmount From vehicle_items 
        Where vehicleID = vehicle;
        
    If itemAmount > 0 Then 
        return 2;
    End If;
    
    Update sessions Set sessions.End = endTime
        Where ID = sessionID;
    Delete From vehicle_items Where vehicleID = vehicle;
    return 0;
END //

/* Why would I map returns. */
Create Or Replace Function AddSessionDriver(sessionId int(10) unsigned, driverId int(10) unsigned) 
returns tinyint(1)
DETERMINISTIC MODIFIES SQL DATA
BEGIN 
    Declare conditionz tinyint(1);

    Select SessionStatus(sessionId) Into conditionz; 
    If conditionz = 1 Then
        return 1;
    End if;

    select CanBeCombined(driverId, sessionId) Into conditionz;
    If conditionz != 0 Then
        return 2;
    End If;

    Update sessions set sessions.driverId = driverId where Id = sessionId;
    return 0;
END //

/*********************************************************************/
/******* Some more easy functions for direct endpoint mapping. *******/


Create Or Replace Function SetCost(itemId int(10) unsigned, price double)
Returns tinyint(1)
DETERMINISTIC MODIFIES SQL DATA
BEGIN
    Update items set cost = price Where Id = itemId;
    return 0;
END //


Create Or Replace Function SetPackaging(itemId int(10) unsigned, packaging int(10) unsigned)
Returns tinyint(1)
DETERMINISTIC MODIFIES SQL DATA
BEGIN
    Update items set amountPerPackaging = packaging Where id = itemId;
    return 0;
END //


Create Or Replace Function SetConditioning(vehicleId int(10) unsigned, conditioning tinyint(1))
Returns tinyint(1)
DETERMINISTIC MODIFIES SQL DATA
BEGIN
    Update vehicles set vehicles.conditioning = conditioning Where id = vehicleId;
    return 0;
END //


Create Or Replace Function SetCoordinates(clientId int(10) unsigned, lat double, lng double)
Returns tinyint(1)
DETERMINISTIC MODIFIES SQL DATA
BEGIN
    Update clients set clients.lat = lat, clients.lng = lng Where id = clientId;
    return 0;
END //

DELIMITER ;


