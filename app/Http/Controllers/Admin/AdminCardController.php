<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminCard;

class AdminCardController extends Controller
{
    // List all admin cards
    public function index()
    {
        return response()->json(
            AdminCard::orderBy('id')->get()
        );
    }

    // register new admin card
    public function store(Request $request)
    {
        $request->validate([
            'card_label' => 'required|string',
            'rfid_uid' => 'required|string|unique:admin_cards,rfid_uid',
            'assigned_to' => 'nullable|string',
        ]);

        $card = AdminCard::create([
            'card_label' => $request->card_label,
            'rfid_uid' => $request->rfid_uid,
            'assigned_to' => $request->assigned_to,
            'status' => 'ACTIVE',
        ]);

        return response()->json($card, 201);
    }

    // update card details (assign, disable, rename)
    public function update(Request $request, AdminCard $card)
    {
        $request->validate([
            'card_label' => 'sometimes|string',
            'assigned_to' => 'sometimes|nullable|string',
            'status' => 'sometimes|in:ACTIVE,DISABLED',
        ]);

        if ($request->has('card_label')) {
            $card->card_label = $request->card_label;
        }

        if ($request->has('assigned_to')) {
            $card->assigned_to = $request->assigned_to;
            $card->assigned_at = now();
        }

        if ($request->has('status')) {
            $card->status = $request->status;
        }

        $card->save();

        return response()->json($card);
    }

    public function destroy(AdminCard $card)
    {
        $card->delete();
        return response()->json(['message' => 'Card deleted']);
    }
}
