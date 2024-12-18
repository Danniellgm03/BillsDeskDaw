<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Invitation;
use App\Notifications\InviteUserNotification;
use Illuminate\Support\Facades\Auth;


class InvitationController extends Controller
{
    public function getInvitations(string $token)
    {
        $invitations = Invitation::where('token', $token)->get();

        return response()->json($invitations);
    }

    public function index()
    {
        $invitations = Invitation::all();

        return response()->json($invitations);
    }


    public function sendInvitation(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:invitations,email',
            'role_id' => 'required|exists:roles,id'
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if(Invitation::where('email', $request->email)->exists()) {
            return response()->json(['error' => 'Invitation already sent to this email'], 422);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $company = $user->company;

        $invitation = Invitation::create([
            'email' => $request->email,
            'role_id' => $request->role_id,
            'company_id' => $company->id,
            'token' => Str::random(32),
        ]);

        $invitation->notify(new InviteUserNotification($invitation));

        return response()->json(['message' => 'Invitation sent successfully.']);
    }

    public function resendInvitation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $invitation = Invitation::where('email', $request->email)->first();

        if (!$invitation) {
            return response()->json(['error' => 'Invitation not found'], 404);
        }

        $invitation->token = Str::random(32);
        $invitation->save();

        $invitation->notify(new InviteUserNotification($invitation));

        return response()->json(['message' => 'Invitation sent successfully.']);
    }

    public function cancelInvitation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $invitation = Invitation::where('email', $request->email)->first();

        if (!$invitation) {
            return response()->json(['error' => 'Invitation not found'], 404);
        }

        $invitation->delete();

        return response()->json(['message' => 'Invitation canceled successfully.']);
    }
}
