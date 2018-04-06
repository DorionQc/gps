<?php

namespace App\Http\Controllers;

use App\Session;
use App\SessionPos;
use App\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\APIs\Google\GoogleDirections;
use App\APIs\Google\Point;

class TruckController extends Controller
{
    /*
        Route::prefix('truck')->group(function() {
        Route::middleware('mustHaveSession')->group(function() {
            Route::post('coords', 'TruckController@sendCoords');
            Route::post('reached', 'TruckController@reachedDest');
            Route::get('destinations', 'TruckController@getDestinations');
            Route::get('inventory', 'TruckController@getInventory');
            Route::get('home', 'TruckController@viewSession');
        });
        Route::get('pickSession', 'TruckController@chooseSession')->middleware('mustHaveNoSession')->name('chooseTruck');
        Route::get('pickSession/{id}', 'TruckController@choseSession')->middleware('mustHaveNoSession')->name('chooseTruck');
    });
    */
    
    private function selectSuitableSessionsForDriver($driverId) {
        return DB::select(
            "select sessionId as id, name as vehicleName, vehicleId, count(commands.id) from commands ".
            "join sessions on sessions.id=sessionid join vehicles on vehicles.id=vehicleid ".
            "where vehicles.licence<=(select licence from drivers where id=1) ".
            "and sessions.start is null group by sessionId, name, vehicleId;"
        );
    }
    
    private function selectSessionClients($sessionId) {
        return DB::select(
            'select name, lat, lng, clients.id as id, complete, commands.id as commandId, date from clients '.
            'join commands on clientId=clients.id where sessionId=?', [$sessionId]
        );
    }
    
    private function selectCommandItems($commandId) {
        return DB::select(
            "select items.name as name, amount, items.id as id, supplierId, cost, conditioning, amountPerPackaging, ".
            "suppliers.name as supplierName from command_items join items on items.id=itemid ".
            "join suppliers on suppliers.id=items.supplierId where commandId=?", [$commandId]
        );
    }
    
    private function selectAllPreviousPositionsOfSession($sessionId) {
        return DB::select(
            "select lat, lng, moment from session_pos where sessionId=?", [$sessionId]
        );
    }
    
    /**
     * Returns an object containing the state of a session (started, in progress, what has been done, finished)
     * @param unknown $sessionId
     */
    private function makeSessionState($sessionId) {
        return DB::select(
            "select ".
                "start is not null as started, ".
                "end is null and start is not null as in_progress, ".
                "(select count(id) from commands where sessionId=? and complete=0)=0 and end is null as waiting_finish, ".
                "end is not null as finished ".
                "from sessions where sessions.id=? group by start, end, sessions.id",
            [$sessionId, $sessionId]
        )[0];
    }
    
    private function selectNotifies($sessionId) {
        return DB::select(
            "select count(*) as hasNotifies from notifies where sessionId=?", [$sessionId]
        );
    }
    
    public function chooseSession(Request $req) 
    {
        // Get all available sessions
        $availableSessions = $this->selectSuitableSessionsForDriver(Auth::user()->id);
        return view('truck.pickSession', ['sessions' => $availableSessions]);
    }
    
    public function getSessionPath(Request $req)
    {
        $data = $req->validate([
            'id' => 'required|exists:sessions,id'
        ]);
        $id = $data['id'];
        return json_encode($this->selectSessionClients($id));
    }
    
    public function getCommandItems(Request $req) {
        $data = $req->validate([
            'id' => 'required|exists:commands,id'
        ]);
        
        return json_encode($this->selectCommandItems($data['id']));
    }
    
    public function choseSession(Request $req, $id)
    {
        $validator = Validator::make(['id' => $id],[
            'id' => 'required|exists:sessions,id'
        ]);
        if ($validator->fails()) {
            return redirect('truck/pickSession');
        }
        
        $req->session()->put('sessionId', $id);
        DB::statement("update sessions set driverId=? where id=?", [Auth::user()->id, $id]);
        
        
        // Make sure the chosen truck is available
        return redirect('truck/session');
    }
    
    public function sendCoords(Request $req) 
    {
        $data = $req->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'moment' => 'required|integer',
        ]);
        $date = date('Y-m-d h:i:s', $data['moment']);
        $sessionId = $req->session()->get('sessionId');
        $dat = [
            'sessionId' => $sessionId,
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'moment' => $date,
        ];
        SessionPos::create($dat);
        
        $notify = $this->selectNotifies($sessionId);
        
        return json_encode(['hasNotifies' => $notify[0]->hasNotifies]);
    }
    
    public function startSession(Request $req) 
    {
        $data = $req->validate([
            'moment' => 'required|integer',
        ]);
        $date = date('Y-m-d h:i:s', $data['moment']);
        $sessionId = $req->getSession()->get('sessionId');
        $success = DB::select("select StartSession(?, ?) as success", [$sessionId, $date]);
        return json_encode(['success' => $success[0]->success, 'state' => $this->makeSessionState($sessionId)]);
    }
    
    public function reachedDest(Request $req) 
    {
        $data = $req->validate([
            'id' => 'integer|required|exists:commands,id'
        ]);
        $sessionId = $req->getSession()->get("sessionId");
        $success = DB::select("Select CompleteDelivery(?, ?) as success", [$sessionId, (int)$data['id']]);
        return json_encode(['success' => $success[0]->success, 'state' => $this->makeSessionState($sessionId)]);
    }
    
    public function finishSession (Request $req)
    {
        $data = $req->validate([
            'moment' => 'required|integer',
        ]);
        $date = date('Y-m-d h:i:s', $data['moment']);
        
        $sessionId = $req->getSession()->get("sessionId");
        $success = DB::select("Select EndSession(?, ?) as success", [$sessionId, $date]);
        
        $req->getSession()->remove('sessionId');
        
        return json_encode(['success' => $success[0]->success, 'state' => $this->makeSessionState($sessionId)]);
    }
    
    public function viewSession(Request $req) 
    {
        $sessionId = $req->session()->get('sessionId');
        
        // Websocket
        
        // Path
        // Té où / chemin fait
        // Commencer
        // Début - Fin
        // Commandes
            // Date
            // Items
            // Client
            // Bouton 'Done'
        // Finir
        DB::statement("delete from notifies where sessionId=?", [$sessionId]);
        
        return view('truck/session', [
            'clients' => $this->selectSessionClients($sessionId), 
            'state' => $this->makeSessionState($sessionId),
            'trace' => $this->selectAllPreviousPositionsOfSession($sessionId)
        ]);
        
    }
}
