<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Moonday;

class ArchiveController extends Controller
{
    //

    public function index() {

        $data = array(
        );

        return view('archive.index', $data);

    }

    public function year($year) {

        $moonday = new Moonday();

        $calendar = array();

        for($i=1;$i<13;$i++) {
            $calendar[$moonday->getMonth($i)] = $moonday->calendarByMonth($year,$i);
        }

        $data = array(
            'year' => $year,
            'calendar' => $calendar
        );

        return view('archive.year', $data);

    }
}
