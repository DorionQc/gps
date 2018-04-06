/* Geremy Desmanche
   List of sql statements that should get useful to our gps-based delivery system.
   2018-02-17
*/

/* Things might be pretty heavy, I don't really care. */

/* Get a list of available sessions for selected driver. */
Select sessionId, vehicleId, COUNT(commands.ID) From commands
    Join sessions On sessions.id = sessionID
    Join vehicles On vehicles.id = vehicleId
    Group By sessionID, vehicleId
    Having SessionStatus(sessionId) = 0
        And CanBeCombined($driver, vehicleId) = 0;


/* Get a list of commands in selected session. */ 
Select * From commands Where sessionId = $session;

/* Get a list of available command for selected session. */
Select id, name, lat, lng, date From commands
    Join clients on clientId = clients.id and sessionId is null
    Where CanContain(commands.id, $session);


/**********************************************************/
/*************** From the list Goulah! gave ***************/

/* Create Item */
("Insert Into items (
        name,
        supplierId,
        cost,
        conditioning,
        amountPerPackaging,
        created_at,
        updated_at
    ) Values (?, ?, ?, ?, ?, NOW(), NOW())", 
$name, $supplier, $cost, $conditioning, $amountPerPackaging)


/* Modify Item Price */
("Select SetCost(?, ?)", $item, $cost)

/* Modify Item Packaging */
("Select SetPackaging(?, ?)", $item, $amountPerPackaging)

/* Create Vehicle */
("Insert Into vehicles (
        name
        licence,
        conditioning,
        capacity,
        created_at,
        update_at
    ) Values (?, ?, ?, NOW(), NOW())",
$licence, $conditioning, $capacity)


/* Modify Vehicle conditioning */
("Select SetConditioning(?, ?)", $vehicle, $conditioning)


/* Create Client */
("Insert into clients (
        name,
        lat,
        lng,
        created_at,
        updated_at
    ) Values (?, ?, ?, NOW(), NOW())", $clientName, $clientLat, $clientLng)


/* Modify Client Coordinates */
("Select SetCoordinates(?, ?)", $client, $clientLat, $clientLng)

/* Create Supplier */
("Insert into suppliers (
        name
    ) Values (?)", $supplierName)

/* Create command */
("Insert into commands (clientId) Values (?)", $client)

/* Add item to command */
("Select AddCommandItem(?, ?, ?)", $command, $item, $amount)


/* Create session */
("Insert into sessions (vehicleId) Values (?)", $vehicle)


/* Get all availables sessions for user */
("Select sessionId, name, COUNT(commands.Id) From commands
    Join sessions On sessions.id = sessionID
    Join vehicles On vehicles.id = vehicleId
    Group By sessionID, name
    Having CanBeCombined(?, vehicleId) = 0", $driver)


/* Start session */
("Select StartSession(?, ?)", $session, $naw)

/* Reach CheckPoint */
("Select CompleteDelivery(?, ?)", $session, $command)

/* Finish session */
("Select EndSession(?, ?)", $session, $naw)









