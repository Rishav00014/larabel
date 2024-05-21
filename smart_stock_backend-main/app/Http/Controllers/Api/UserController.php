<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request){
        $request->validate([
            "email" => "required|email|string",
            "password" => "required"
        ]);
        $user = User::where("email", $request->email)->first();

        if (!empty($user)) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken("myToken")->accessToken;
                return response()->json([
                    "status" => true,
                    "message" => "User Login Successfully",
                    "token" => $token,
                    "data" => []
                ], 200);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Password Did Not Match",
                    "data" => []
                ], 404);
            }
        } else {
            return response()->json([
                "status" => false,
                "message" => "Invaild Email Value",
                "data" => []
            ], 404);
        }
    }

    public function createUser(Request $request)
    {
        $validate = $request->validate([
            "firstname" => "required|string|min:3",
            "lastname" => "required|string|min:3",
            "email" => "required|email|unique:users,email",
            "mobile" => "required|min:10|max:10",
            "password" => "required|min:6",
            // "password_confirmation" => "required",
        ]);
        $user = User::create($validate);        
        // $token = $user->createToken("laravel")->accessToken;
        return response()->json([
            // "token" => $token,
            "message" => "User Register SuccessFully",
            "status" => 1,
            "data" => $user,
        ], 200);       
    }

    public function getAllUsers()
    {
      $user = User::all();
      return response()->json(['status' => true, 'user' => $user]);
    }


    public function createCustomer(Request $request)
{
    $validate = $request->validate([
        "name" => "required|string|min:3|unique:customers,name",
        "email" => "nullable|email",
        "mobile" => "nullable|min:10|max:10",
        "address" => "nullable|string|min:3",
        "description" => "nullable|string|min:3",
    ]);

    $customer = new Customer($validate);
    $customer->user_id = auth()->id(); // Assign the authenticated user's ID
    $customer->save();

    return response()->json([
        "message" => "Customer Created Successfully",
        "status" => 1,
        "data" => $customer,
    ], 200);
}


public function calculateTotals()
    {
        $userId = auth()->id();

        // Fetch journals related to the authenticated user
        $journals = Journal::where('user_id', $userId)->get();

        // Calculate totals
        $totalDebit = $journals->sum('debit');
        $totalCredit = $journals->sum('credit');
        $totalBalance = $totalDebit - $totalCredit;

        // Return the results
        return response()->json([
            'status' => true,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'total_balance' => $totalBalance
        ], 200);
    }

public function getAllCustomers()
{
    $userId = auth()->id();

    // Fetching customers related to the authenticated user
    $customers = Customer::where('user_id', $userId)->get();

    if ($customers->isEmpty()) {
        return response()->json(['status' => false, 'message' => 'Customers Not Found!']);
    } else {
        return response()->json(['status' => true, 'customers' => $customers]);
    }
}

public function deleteAllJournals()
{
    $userId = auth()->id();

    // Fetch and delete journals related to the authenticated user
    $deleted = Journal::where('user_id', $userId)->delete();

    if ($deleted) {
        return response()->json([
            'status' => true,
            'message' => 'All journals created by the current user have been deleted.'
        ], 200);
    } else {
        return response()->json([
            'status' => false,
            'message' => 'No journals found for the current user.'
        ], 404);
    }
}

       public function createJournal(Request $request)
    {
        $payload = $request->only(['customerId', 'customerId2', 'credit', 'debit', 'transfer', 'details', 'type']);

        // Extract user_id from the current authenticated user
        $user = $request->user(); // Assuming you're using Laravel's built-in authentication
        $user_id = $user->id;

        // Set credit, debit, and transfer values based on the type
        if ($payload['type'] === 'transfer') {
            $credit = 0;
            $debit = 0;
            $transfer = $payload['transfer'];
            $customerId2 = $payload['customerId2']; // Ensure customerId2 is provided
        } else {
            // If type is 'normal', fill in missing debit or credit values with 0
            $credit = isset($payload['credit']) ? $payload['credit'] : 0;
            $debit = isset($payload['debit']) ? $payload['debit'] : 0;
            $transfer = 0;
            $customerId2 = null; // Not used for normal type
        }

        // Create Journal
        $journal = new Journal();
        $journal->customerId = $payload['customerId'];
        $journal->user_id = $user_id;
        $journal->credit = $credit;
        $journal->debit = $debit;
        $journal->transfer = $transfer;
        $journal->details = $payload['details'];
        $journal->type = $payload['type']; // Set the type
        $journal->customerId2 = $customerId2;

        try {
            $journal->save();
            return response()->json(['status' => true, 'message' => 'Journal created successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to create journal', 'error' => $e->getMessage()]);
        }
    }

    public function getAllJournals()
{
    // Assuming you're using Laravel's authentication system
    $userId = auth()->id();

    // Fetching journal entries created by the authenticated user
    $journal_entries = Journal::with(['user', 'customer', 'customer2'])
        ->where('user_id', $userId)
        ->get();

    return response()->json(['status' => true, 'data' => $journal_entries], 200);
}


      public function getByIdandDate(Request $request){
        $user = Journal::where(function ($query) use ($request) {
            if (!empty ($request->customerId)) {
                $query->where('customerId', $request->customerId);
            }
        })->where(function ($query) use ($request) {
            if (!empty ($request->user_id)) {
                $query->where('user_id', $request->user_id);
            }
        })->where(function ($query) use ($request) {
            if (!empty ($request->dateFrom) && !empty ($request->dateTo)) {
                $query->whereBetween('created_at',[$request->dateFrom,$request->dateTo]);
            }
        })->get();
        return response()->json(['status' => true, 'user' => $user]);
      }

     

      public function logout()
    {
        $token = auth()->user()->token();
        $token->revoke();
        return response()->json([
            "status"=> true,
            "message"=> "User Logged Out Successfully"
            ], 200);
    }
}
