<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function toggle(Request $request)
    {
        $status = Status::first();
        $status->status = $status->status === Status::WRITING ? Status::DONE : Status::WRITING;
        $status->save();

        return response()->json([
            'status' => 'success',
            'newStatus' => $status->status
        ]);
    }
}
