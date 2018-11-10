<?php

namespace App\Controllers;

use App\Models\Report;

class ReportController extends Controller {

    public function reportUser($request, $response) {
        $response = $response->withJson(Report::reportUser($request, $response, $this->container['conn']));
        return $response;
    }

}