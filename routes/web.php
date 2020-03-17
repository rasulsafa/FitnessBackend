<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\User;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/get_user_data', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'username' => 'required',
        'password' => 'required',
    ]);
    if ($validator->fails()) {    
        return response()->json(['error' => $validator->messages()], 200);
    }
    $user = User::where('username' , $request->username)->first();
    if($user && Hash::check($request->password, $user->password)) {
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'date_of_birth' => $user->date_of_birth,
            'weight' => $user->weight,
            'height' => $user->height,
            'sex' => $user->sex,
            'latitude' => $user->latitude,
            'longitude' => $user->longitude,
            'weight_goal' => $user->weight_goal,
            'home_address' => $user->home_address,
            'workouts' => $user->workouts()->get()
        ]);
    } 
    return response()->json([
        'error' => 'Incorrect login credentials' 
    ]);
});

Route::get('/start_workout', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'username' => 'required',
        'password' => 'required',
        'workout_id' => 'required',
    ]);
    if ($validator->fails()) {    
        return response()->json(['error' => $validator->messages()], 200);
    }
    $user = User::where('username' , $request->username)->first();
    if($user && Hash::check($request->password, $user->password)) {
        $workout = $user->workouts()->where('id', $request->workout_id);
        if($workout) {
            $workout->in_progress = true;
            $workout->save();
            return response()->json([
                'success' => 'Workout started'
            ]);
        }
        return response()->json([
            'error' => 'Workout not found'
        ]);
    } 
    return response()->json([
        'error' => 'Incorrect login credentials'
    ]);
});
Route::get('/update_workout_progress', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'username' => 'required',
        'password' => 'required',
        'workout_id' => 'required',
        'new_progress_level' => 'required',
    ]);
    if ($validator->fails()) {    
        return response()->json(['error' => $validator->messages()], 200);
    }
    $user = User::where('username' , $request->username)->first();
    if($user && Hash::check($request->password, $user->password)) {
        $workout = $user->workouts()->where('id', $request->workout_id);
        if($workout) {
            if($workout->in_progress) {
                $workout->progress = $request->new_progress_level;
                $workout->save();
            }
            return response()->json([
                'error' => 'Workout not in progress'
            ]);
        }
        return response()->json([
            'error' => 'Workout not found'
        ]);
    } 
    return response()->json([
        'error' => 'Incorrect login credentials'
    ]);
});

Route::get('/end_workout', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'username' => 'required',
        'password' => 'required',
        'workout_id' => 'required',
    ]);
    if ($validator->fails()) {    
        return response()->json(['error' => $validator->messages()], 200);
    }
    $user = User::where('username' , $request->username)->first();
    if($user && Hash::check($request->password, $user->password)) {
        $workout = $user->workouts()->where('id', $request->workout_id);
        if($workout) {
            $workout->in_progress = false;
            $workout->completed = true;
            $workout->date_completed = Carbon::now()->toDateTimeString();
            $workout->save();
            return response()->json([
                'success' => 'Workout ended' 
            ]);
        }
        return response()->json([
            'error' => 'Workout not found' 
        ]);
    } 
    return response()->json([
        'error' => 'Incorrect login credentials' 
    ]);
});
Route::get('/workout_history', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'username' => 'required',
        'password' => 'required'
    ]);
    if ($validator->fails()) {    
        return response()->json(['error' => $validator->messages()], 200);
    }
    $user = User::where('username' , $request->username)->first();
    if($user && Hash::check($request->password, $user->password)) {
        return response()->json($user->workouts()->where('completed', true)->get());
    } 
    return response()->json([
        'error' => 'Incorrect login credentials' 
    ]);
});
Route::get('/in_progress_workouts', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'username' => 'required',
        'password' => 'required'
    ]);
    if ($validator->fails()) {    
        return response()->json(['error' => $validator->messages()], 200);
    }
    $user = User::where('username' , $request->username)->first();
    if($user && Hash::check($request->password, $user->password)) {
        return response()->json($user->workouts()->where('in_progress', true)->get());
    } 
    return response()->json([
        'error' => 'Incorrect login credentials' 
    ]);
});
Route::get('/available_workouts', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'username' => 'required',
        'password' => 'required'
    ]);
    if ($validator->fails()) {    
        return response()->json(['error' => $validator->messages()], 200);
    }
    $user = User::where('username' , $request->username)->first();
    if($user && Hash::check($request->password, $user->password)) {
        return response()->json($user->workouts()->where('in_progress', false)->where('completed', false)->get());
    } 
    return response()->json([
        'error' => 'Incorrect login credentials' 
    ]);
});
Route::get('/weather', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'latitude' => 'required',
        'longitude' => 'required',
    ]);
    if ($validator->fails()) {    
        return response()->json(['error' => $validator->messages()], 200);
    }
    $ch = curl_init('https://www.metaweather.com/api/location/search/?lattlong=' . $request->latitude . "," . $request->longitude);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
    curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $result = curl_exec($ch);
    curl_close($ch);
    
    $ch = curl_init('https://www.metaweather.com/api/location/' . json_decode($result)[0]->woeid);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
    curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $result = curl_exec($ch);
    curl_close($ch);
    
    
    return response()->json(json_decode($result)->consolidated_weather[0]);
});
Route::get('/get_yelp_data', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'latitude' => 'required',
        'longitude' => 'required',
    ]);
    if ($validator->fails()) {
        return response()->json(['error' => $validator->messages()], 200);
    }
    $authorization = "Authorization: Bearer qdULecXysnk6x2ghkxZrhd5nR5xUk8n4VOB9HvMPyiyp1GVQforaxDbtZ7dDJ1m5WCTqD-3wu8fvLZS8CA2Dir67THAvs0stDY7_NvVbY5zYgjfI0KalkcnTnnNwXnYx";
    
    try {
    
        $ch = curl_init('https://api.yelp.com/v3/businesses/search?term=healthy&radius=16093&limit=5&sort_by=rating&open_now=true&latitude=' . $request->latitude . "&longitude=" . $request->longitude);
        
        if ($ch === false) {
           throw new Exception('failed to initialize');
       }
            
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
        curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        if ($result === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
        curl_close($ch);
        return response()->json(json_decode($result));
        
    } catch(Exception $e) {

    trigger_error(sprintf(
        'Curl failed with error #%d: %s',
        $e->getCode(), $e->getMessage()),
        E_USER_ERROR);

    }

});

// /register?username=rasulsafa&password=123&email=rasulsafa@gmail.com&weight=140&sex=m&height=173&date_of_birth=10091999&home_address=Aldrich+Hall+Irvine,+CA+92697&weight_goal=135
Route::get('/register', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'unique:users|required',
        'username' => 'unique:users|required',
        'password' => 'required',
        'height' => 'required',
        'weight' => 'required',
        'sex' => 'required',
        'date_of_birth' => 'required',
        'home_address' => 'required',
        'weight_goal' => 'required',
    ]); 
    if ($validator->fails()) {    
        return response()->json(['error' => $validator->messages()], 200);
    }

    $user = new User;
    $user->password = Hash::make($request->password);
    $user->username = $request->username;
    $user->email = $request->email;
    $user->height = $request->height;
    $user->weight = $request->weight;
    $user->sex = $request->sex;
    $user->date_of_birth = $request->date_of_birth;
    $user->home_address = $request->home_address;
    $user->weight_goal = $request->weight_goal;
    $user->save();
    
    $workout1 = new App\Workout(['name' => 'Walk 2 Miles', 'description' => 'Go walking for 2 Miles', 'completed' => false, 
    'in_progress' => false, 'unit' => 'Miles', 'progress' => 0, 'goal_amount' => 4, 'exercise_equation_multiplier' => 10, 'outdoor_activity' => false]);
    
    $workout2 = new App\Workout(['name' => 'Do 10 Pushups', 'description' => 'Do 10 Pushups', 'completed' => false, 
    'in_progress' => false, 'unit' => 'Pushups', 'progress' => 0, 'goal_amount' => 10, 'exercise_equation_multiplier' => 10, 'outdoor_activity' => false]);
    
    $user->workouts()->save($workout1);
    $user->workouts()->save($workout2);
    $user->save();

    return response()->json([
        'success' => 'User successfully registered'
    ]);
});
