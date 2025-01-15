<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TennisCourt;
use Illuminate\Http\Request;

class TennisCourtController extends ApiController
{
    public function index()
    {
        $tennisCourts = TennisCourt::all();
        return $this->handleResponse($tennisCourts, 'Tennis courts retrieved successfully.');
    }


    public function show($id)
    {
        $tennisCourt = TennisCourt::find($id);

        if (is_null($tennisCourt)) {
            return $this->handleResponseMessage('Tennis court not found.');
        }

        return $this->handleResponse($tennisCourt, 'Tennis court retrieved successfully.');
    }

}
