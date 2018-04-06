<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Item;
use App\Session;
use App\Vehicle;
use App\Client;
use App\Command;
use App\VehicleItem;
use App\Supplier;

use Braintree;
use PHPMailer\PHPMailer\PHPMailer;

class AdminController extends Controller
{
    /*
            Route::get('items', 'AdminController@getItem')                   ->name('makeItem');
            Route::get('trucks', 'AdminController@getTruck')                 ->name('makeTruck');
            Route::get('clients', 'AdminController@getClient')               ->name('makeClient');
            Route::get('commands', 'AdminController@getCommand')             ->name('makeCommand');
            Route::get('sessions', 'AdminController@getSession')             ->name('makeSession');
            Route::get('suppliers', 'AdminController@getSupplier')           ->name('makeSupplier');
                                                                             
            Route::post('items', 'AdminController@MakeItem')                 ->name('postItem');    
            Route::post('trucks', 'AdminController@MakeTruck')               ->name('postTruck');   
            Route::post('clients', 'AdminController@MakeClient')             ->name('postClient');  
            Route::post('commands', 'AdminController@MakeCommand')           ->name('postCommand'); 
            Route::post('sessions', 'AdminController@MakeSession')           ->name('postSession'); 
            Route::post('suppliers', 'AdminController@MakeSupplier')         ->name('postSupplier');
                                                                             
            Route::get('trucks/{id}', 'AdminController@getEditTruck')        ->name('makeTruck');
            Route::get('items/{id}', 'AdminController@getEditItem')          ->name('makeItem');
            Route::get('suppliers/{id}', 'AdminController@getEditSupplier')  ->name('makeSupplier');
            Route::get('clients/{id}', 'AdminController@getEditClient')      ->name('makeClient');
            Route::get('commands/{id}', 'AdminController@getEditCommand')    ->name('makeCommand');
            Route::get('sessions/{id}', 'AdminController@getEditSession')    ->name('makeSession');
            
            Route::post('editItemInInventory', 'AdminController@EditInventory');
            Route::post('editItemInCommand', 'AdminController@EditCommand');
     */
    
    
    
    /* selects */
    
    private function selectVehicles() {
        return DB::select("select id, licence, name, conditioning, capacity, usedCapacity, IsOnTheRoad(id) as isOnTheRoad from vehicles");
    }
    private function selectItems() {
        return DB::select("select ".
            "i.id as id, i.name as name, i.supplierId as supplierId, i.cost as cost, 1 as amount, ".
            "i.conditioning as conditioning, i.amountPerPackaging as amountPerPackaging, s.name as supplierName ".
            "from items i join suppliers s on s.id=i.supplierId");
    }
    private function selectSuppliers() {
        return DB::select("select * from suppliers");
    }
    private function selectClients() {
        return DB::select("select * from clients");
    }
    private function selectClientLabels() {
        return DB::select("select id, name from clients");
    }
    private function selectCommands() {
        return DB::select("select date, name, commands.id as id, count(command_items.commandId) as item_count from commands ".
            "join clients on clients.id=clientid join command_items on command_items.commandId=commands.id where complete=0 ".
            "group by date, name, id");
    }
    private function selectCommandItems($commandId) {
        return DB::select(
            "select items.name as name, amount, items.id as id, supplierId, cost, conditioning, amountPerPackaging, ".
            "suppliers.name as supplierName from command_items join items on items.id=itemid ".
            "join suppliers on suppliers.id=items.supplierId where commandId=?", [$commandId]
        );
    }
    private function selectSessions() {
        return DB::select("select sessions.id as id, start, vehicles.name as vehicleName ".
            "from sessions join vehicles on vehicles.id = vehicleId where end is null");
    }
    private function selectDrivers() {
        return DB::select("select id, name, lastName, licence, email, isAdmin from drivers");
    }
    private function selectSessionCommands($sessionId) {
        return DB::select(
            "select commands.id as id, clientId, sessionId, name, lat, lng, complete, date, count(command_items.commandId) as item_count ".
            "from commands join clients on clients.id=commands.clientId join command_items on command_items.commandId=commands.id ".
            "where commands.sessionId=? group by commands.id, clientId, name, sessionId, complete, date, lat, lng", [$sessionId]
        );
    }
    private function selectCommandSuitableForSession($sessionId) {
        return DB::select(
            "Select commands.id as id, clientId, sessionId, name, lat, lng, date, complete, count(command_items.commandId) as item_count From commands ".
            "Join clients on clientId = clients.id and sessionId is null join command_items on command_items.commandId=commands.id ".
            "Where CanContain(commands.id, (select vehicleId from sessions where id=?))=0 group by commands.id, clientId, sessionId, name, lat, lng, complete, date", [$sessionId]
        );
    }
    private function selectAvailableVehicles() {
        return DB::select(
            "select name, id from vehicles ".
            "where id not in (Select vehicleId from sessions where end is null)"
        );
    }
    private function selectInventoryForVehicle($vehicleId) {
        return DB::select(
            "select name, amountPerPackaging, amount, ceil(amount / amountPerPackaging) as packaging, trueAmount as virtualAmount ".
            "from vehicle_items join items on id=itemId where vehicleId=?", 
            [$vehicleId]
        );
    }
    private function selectAllOfThem($lastId) {
        $vehicles = DB::select (
            "select vehicles.name as name, vehicles.id as vehicleId, IsOnTheRoad(vehicles.id) as IsOnTheRoad, sessions.id as sessionId, concat(drivers.name, ' ', lastName) as driverName, phoneNumber From vehicles". 
            " Left Join sessions On sessions.vehicleId = vehicles.id and end is null left join drivers on drivers.id=driverId "  
        );
        
        $data = [];
        foreach ($vehicles as $vehicle) {
            if ($vehicle->sessionId == null) {
                $data[$vehicle->vehicleId] = [
                    'name' => $vehicle->name,
                    'driverName' => "",
                    'driverPhone' => 0,
                    'isOnTheRoad' => false,
                    'commands' => null,
                    'positions' => [(object) ['id'=>1, 'moment'=>date('Y-m-d h:i:s'), 'lat'=>46.816695, 'lng'=>-71.1516221]]
                ];
            }
            else {
                $positions = DB::select(
                    "select id, moment, lat, lng from session_pos where sessionId = ? and id > ?", [$vehicle->sessionId, $lastId]
                );
                $data[$vehicle->vehicleId] = [
                    'name' => $vehicle->name,
                    'driverName' => $vehicle->driverName,
                    'driverPhone' => $vehicle->phoneNumber,
                    'isOnTheRoad' => $vehicle->IsOnTheRoad,
                    'commands' => DB::select(
                        "select commands.id, clientId, lat, lng, date, clients.name, complete, count(command_items.itemId) as itemCount ".
                        "from commands join clients on clients.Id = clientId join command_items on command_items.commandId=commands.id ".
                        "where sessionId = ? group by commands.id, clientId, lat, lng, date, clients.name, complete", [$vehicle->sessionId]
                        ),
                    'positions' => count($positions) == 0 
                                   ? [(object) ['id'=>1, 'moment'=>date('Y-m-d h:i:s'), 'lat'=>46.816695, 'lng'=>-71.1516221]]
                                   : $positions
                ];
            }
        }
        return $data;
    }
    
    
    
    /* getters */
    
    public function getItem(Request $req) {
        return view('controller.items', ['items' => $this->selectItems(), 'suppliers' => $this->selectSuppliers()]);
    }
    public function getTruck(Request $req) {
        return view('controller.trucks', ['vehicles' => $this->selectVehicles()]);
    }
    public function getClient(Request $req) {
        return view('controller.clients', ['clients' => $this->selectClients()]);
    }
    public function getCommand(Request $req) {
        return view('controller.commands', [
            'commands' => $this->selectCommands(), 
            'clients' => $this->selectClientLabels(),
            'items' => $this->selectItems()
        ]);
    }
    public function getSession(Request $req) {
        return view('controller.sessions', [
            'sessions' => $this->selectSessions(),
            'vehicles' => $this->selectAvailableVehicles(),
        ]);
    }
    public function getSupplier(Request $req) {
        return view('controller.suppliers', ['suppliers' => $this->selectSuppliers()]);
    }
    
    
    
    
    /* edit pages */
    
    
    public function getEditTruck(Request $req, $id) {
        $validator = Validator::make(['id' => $id],[
            'id' => 'required|exists:vehicles,id'
        ]);
        if ($validator->fails()) {
            return redirect('controller/trucks');
        }
        $vehicle = Vehicle::find($id);
        return view(
            'controller.trucks', 
            ['vehicles' => $this->selectVehicles(), 'current' => $vehicle, 'inventory' => $this->selectInventoryForVehicle($vehicle->id)]
        );
    }
    public function getEditItem(Request $req, $id) {
        $validator = Validator::make(['id' => $id],[
            'id' => 'required|exists:items,id'
        ]);
        if ($validator->fails()) {
            return redirect('controller/items');
        }
        $item = Item::find($id);
        return view('controller.items', ['items' => $this->selectItems(), 'current' => $item, 'suppliers' => $this->selectSuppliers()]);
    }
    public function getEditSupplier(Request $req, $id) {
        $validator = Validator::make(['id' => $id],[
            'id' => 'required|exists:suppliers,id'
        ]);
        if ($validator->fails()) {
            return redirect('controller/suppliers');
        }
        $supplier = Supplier::find($id);
        return view('controller.suppliers', ['suppliers' => $this->selectSuppliers(), 'current' => $supplier]);
    }
    public function getEditClient(Request $req, $id) {
        $validator = Validator::make(['id' => $id],[
            'id' => 'required|exists:clients,id'
        ]);
        if ($validator->fails()) {
            return redirect('controller/clients');
        }
        $client = Client::find($id);
        return view('controller.clients', ['clients' => $this->selectClients(), 'current' => $client]);
    }
    public function getEditCommand(Request $req, $id) {
        $validator = Validator::make(['id' => $id],[
            'id' => 'required|exists:commands,id'
        ]);
        if ($validator->fails()) {
            return redirect('controller/commands');
        }
        $command = Command::find($id);
        $items = $this->selectItems();
        $current_items = $this->selectCommandItems($command->id);
        
        foreach($current_items as $i) {
            foreach($items as $i2) {
                if ($i->id == $i2->id) {
                    $i2->amount = $i->amount;
                    break;
                }
            }
        }
        
        return view('controller.commands', [
            'commands' => $this->selectCommands(), 
            'clients' => $this->selectClientLabels(), 
            'items' => $items,
            'current' => $command,
            'current_items' => $current_items,
        ]);
    }
    public function getEditSession(Request $req, $id) {
        $validator = Validator::make(['id' => $id],[
            'id' => 'required|exists:sessions,id'
        ]);
        if ($validator->fails()) {
            return redirect('controller/sessions');
        }
        $session = Session::find($id);
        $current_commands = $this->selectSessionCommands($id);
        
        return view('controller.editSessions', [
            'sessions' => $this->selectSessions(),
            'commands' => $this->selectCommandSuitableForSession($id),
            'current' => $session,
            'current_commands' => $current_commands,
        ]);
    }
    
    
    
    
    
    /* makers */
    
    public function MakeItem(Request $req) {
        if ($req->has('id') && $req->input('id')) {
            $data = $req->validate([
                'name' => ['required', 'string', 'max:32', Rule::unique('items')->ignore($req->input('id'))],
                'amountPerPackaging' => 'required|integer|min:0',
                'conditioning' => 'sometimes',
                'supplierId' => 'required|integer|exists:suppliers,id',
                'cost' => 'required|numeric|min:0',
                'id' => 'required|exists:items,id'
            ]);
            $data['conditioning'] = $req->has('conditioning');
            $id = $req->input('id');
            Item::find($id)->update($data);
        } else {
            $data = $req->validate([
                'name' => 'required|string|max:32|unique:items',
                'amountPerPackaging' => 'required|integer|min:0',
                'conditioning' => 'sometimes',
                'supplierId' => 'required|integer|exists:suppliers,id',
                'cost' => 'required|numeric|min:0',
            ]);
            $data['conditioning'] = $req->has('conditioning');
            Item::create($data);
        }
        return redirect('controller/items');
    }
    public function MakeTruck(Request $req) {
        if ($req->has('id') && $req->input('id')) {
            $data = $req->validate([
                'name' => ['required', 'string', 'max:32', Rule::unique('vehicles')->ignore($req->input('id'))],
                'licence' => 'required|integer|in:1,5',
                'capacity' => 'required|integer|min:0',
                'conditioning' => 'sometimes',
                'id' => 'required|exists:vehicles,id'
            ]);
            $data['conditioning'] = $req->has('conditioning');
            $id = $req->input('id');
            Vehicle::find($id)->update($data);
        } else {
            $data = $req->validate([
                'name' => 'required|string|max:32|unique:vehicles',
                'licence' => 'required|integer|in:1,5',
                'conditioning' => 'sometimes',
                'capacity' => 'required|integer|min:0'
            ]);
            $data['conditioning'] = $req->has('conditioning');
            Vehicle::create($data);
        }
        return redirect('controller/trucks');
    }
    public function MakeSupplier(Request $req) {
        if ($req->has('id') && $req->input('id')) {
            $data = $req->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('suppliers')->ignore($req->input('id'))],
                'id' => 'exists:suppliers,id'
            ]);
            $id = $req->input('id');
            Supplier::find($id)->update($data);
        } else {
            $data = $req->validate([
                'name' => 'required|string|max:255|unique:suppliers',
            ]);
            Supplier::create($data);
        }
        return redirect('controller/suppliers');
    }
    public function MakeClient(Request $req) {
        if ($req->has('id') && $req->input('id')) {
            $data = $req->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('clients')->ignore($req->input('id'))],
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'id' => 'required|exists:suppliers,id'
            ]);
            $id = $req->input('id');
            Client::find($id)->update($data);
        } else {
            $data = $req->validate([
                'name' => 'required|string|max:255|unique:clients',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
            ]);
            Client::create($data);
        }
        return redirect('controller/clients');
    }
    public function MakeCommand(Request $req) {
        if ($req->has('id') && $req->input('id')) {
            $data = $req->validate([
                'clientId' => 'required|exists:clients,id',
                'id' => 'required|exists:commands,id',
                'items' => 'required|array|min:1|max:18',
                'items.*.id' => 'required|integer|distinct',
                'items.*.amount' => 'required|integer|min:1',
            ]);
            $id = intval($req->input('id'));
            DB::statement("delete from command_items where commandId=:id", ['id' => $id]);
            Command::find($id)->update($data);
            foreach($data['items'] as $item) {
                DB::select("select AddCommandItem(?, ?, ?)", [$id, intval($item['id']), intval($item['amount'])]);
            }
        } else {
            $data = $req->validate([
                'clientId' => 'required|exists:clients,id',
                'items' => 'required|array|min:1|max:18',
                'items.*.id' => 'required|integer|distinct',
                'items.*.amount' => 'required|integer|min:1',
            ]);
            $command = Command::create($data);
            foreach($data['items'] as $item) {
                DB::select("select AddCommandItem(?, ?, ?)", [$command->id, intval($item['id']), intval($item['amount'])]);
            }
        }
        return redirect('controller/commands');
    }
    public function MakeSession(Request $req) {
        $data = $req->validate([
            'vehicleId' => 'required|exists:vehicles,id',
        ]);
        $vehiclesInSession = DB::select("select count(id) from sessions where end is not null and vehicleId=?", [$data['vehicleId']]);
        $session = Session::create($data);
        $id = $session->id;
        
        return redirect("controller/sessions/$id");
    }
    public function EditSession(Request $req) {
        error_log(json_encode($req->all()));
        $data = $req->validate([
            'id' => 'required|exists:sessions,id',
            'commands' => 'sometimes|array',
            'commands.*' => 'required_with:commands|integer|distinct|exists:commands,id',
        ]);
        
        DB::transaction(function() use ($data) {
            $id = $data['id'];
            DB::statement('update commands set sessionId=null where sessionId=?', [$id]);
            if (isset($data['commands'])) {
                foreach($data['commands'] as $commId) {
                    DB::statement('update commands set sessionId=? where id=?', [$id, $commId]);
                }
            }
            DB::statement('insert ignore into notifies values (?)', [$id]);
        });
        
        return redirect("controller/sessions");
    }
    
    
    public function GetHome(Request $req) {
        
        // Get all current paths
        // Get the status of all vehicles
        // Get all ongoing sessions
        
        return view('controller.home');
    }
    
    public function GetHomeData (Request $req) {
        $lastId = 0;
        if ($req->has('lastId')) {
            $lastId = intval($req->get('lastId'));
        }
        
        return json_encode($this->selectAllOfThem($lastId));
    }
    
    
    
    
    private function configureBraintree() {
        Braintree\Configuration::environment('sandbox');
        Braintree\Configuration::merchantId(env('BRAINTREE_MERCHANT_ID'));
        Braintree\Configuration::publicKey(env('BRAINTREE_PUBLIC_KEY'));
        Braintree\Configuration::privateKey(env('BRAINTREE_PRIVATE_KEY'));
        
    }
    
    public function Pay(Request $req) {
        //$this->configureBraintree();
        $gateway = new Braintree\Gateway([
                'environment' => 'sandbox',
                'merchantId' => env('BRAINTREE_MERCHANT_ID'),
                'publicKey' =>  env('BRAINTREE_PUBLIC_KEY'),
                'privateKey' => env('BRAINTREE_PRIVATE_KEY')
            ]);

        return view('braintree/pay', ['braintree_key' => $gateway->clientToken()->generate(), 'suppliers'=>DB::select('select id, name,  bill from suppliers')]);
    }
    
    public function SendMail(Request $req, $id, $bill) {
        
        
        $mail = new PHPMailer;
        
        //Enable SMTP debugging.
        //$mail->SMTPDebug = 3;
        //Set PHPMailer to use SMTP.
        $mail->isSMTP();
        //Set SMTP host name
        $mail->Host = "smtp.gmail.com";
        //Set this to true if SMTP host requires authentication to send email
        $mail->SMTPAuth = true;
        //Provide username and password
        $mail->Username = env('MAIL_USERNAME');
        $mail->Password = env('MAIL_PASSWORD');
        //If SMTP requires TLS encryption then set it
        $mail->SMTPSecure = "tls";
        //Set TCP port to connect to
        $mail->Port = 587;
        
        $mail->From = "clonecharle3@gmail.com";
        $mail->FromName = "Charles Cote";
        
        $mail->addAddress(Auth::user()->email, "Recepient Name");
        
        $mail->isHTML(true);
        
        $mail->Subject = "Subject Text";
        $mail->Body = "<span>Identifiant : $id, Montant : $bill. </span><a href='https://youtu.be/3h0zccQXCPc'>Gotta check da vidjeo</a>";
        $mail->AltBody = "Identifiant : $id, Montant : $bill";
        
        if(!$mail->send())
        {
            return "\n\nMailer Error: " . $mail->ErrorInfo;
        }
        else
        {
            return "Message has been sent successfully";
        }
    }
    
    
    public function FinishTransaction(Request $req) {
        //$this->configureBraintree();
        $nonce = $req->post('payment_methode_nonce');
        
        $data = $req->validate([
            'id' => 'required|exists:suppliers,id',
            'bill' => 'required|numeric|min:0'
        ]);
        $id = $req->input('id');
        
        $bill = DB::select("select bill from suppliers where id=?", [$id])[0]->bill;
        if (abs($bill - $data['bill']) > 0.001) {
            return "Error message : Amount did not match our records.";
        }
        
        if (isset($nonce)) {


            /*$gateway = new Braintree\Gateway([
                'environment' => 'sandbox',
                'merchantId' => env('BRAINTREE_MERCHANT_ID'),
                'publicKey' =>  env('BRAINTREE_PUBLIC_KEY'),
                'privateKey' => env('BRAINTREE_PRIVATE_KEY')
            ]);*/

	        $gateway = new Braintree\Gateway([
                'environment' => 'sandbox',
                'merchantId' => env('BRAINTREE_MERCHANT_ID'),
                'publicKey' =>  env('BRAINTREE_PUBLIC_KEY'),
                'privateKey' => env('BRAINTREE_PRIVATE_KEY')
            ]);

 
            $result = $gateway->transaction()->sale([
                "amount" => $bill,
                //"merchantAccountId" => 'CAD',
                "paymentMethodNonce" => 'fake-valid-nonce', //$nonce,
                /*"orderId" => 1,
                "descriptor" => [
                    "name" => "22 char max"
                ],
                "shipping" => [
                    "firstName" => "Jen",
                    "lastName" => "Smith",
                    "company" => "Braintree",
                    "streetAddress" => "1 E 1st St",
                    "extendedAddress" => "Suite 403",
                    "locality" => "Bartlett",
                    "region" => "IL",
                    "postalCode" => "60103",
                    "countryCodeAlpha2" => "US"
                ],
                "options" => [
                    "paypal" => [
                        "customField" => "Cout",
                        "description" => ""
                    ],
                ]*/
            ]);
            //return json_encode($result);
            //$this->SendMail($req);
            if ($result->success) {
                $this->SendMail($req, $result->transaction->id, $bill);
                DB::statement("UPDATE suppliers SET bill=0 WHERE id=?", [$id]);
                return "Success ID: " . $result->transaction->id . ", Amount : $bill";
            } else {
                return "Error Message: " . $result->message;
            }
        }
        
    }
}
