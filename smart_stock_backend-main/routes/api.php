<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('login',[UserController::class,'login']);
Route::post('createUser',[UserController::class,'createUser']);



Route::group(["middleware"=>["auth:api"]], function () {
    Route::get('getAllUsers',[UserController::class,'getAllUsers']);    
    Route::get('getAllCustomers',[UserController::class,'getAllCustomers']);
    Route::post('createCustomer',[UserController::class,'createCustomer']);
    Route::post('/createJournal', [UserController::class, 'createJournal']);
    Route::get('/getAllJournals', [UserController::class, 'getAllJournals']);
    Route::get('totals', [UserController::class, 'calculateTotals']);
    Route::get('/deleteJournals', [UserController::class, 'deleteAllJournals']);
    Route::get("logout",[UserController::class,"logout"]);




    Route::get('getByIdandDate',[UserController::class,'getByIdandDate']);    

});


