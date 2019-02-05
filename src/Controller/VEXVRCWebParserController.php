<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

/**
 * Description of VEXVRCWebParserController
 *
 * @author annon
 */
class VEXVRCWebParserController extends VEXIQWebParserController{
    //put your code here
    const HIGH_SCHOOL = 'High School';
    const SCHOOL_LEVELS = array(VEXVRCWebParserController::MIDDLE_SCHOOL, VEXVRCWebParserController::HIGH_SCHOOL);
    const WORLD_RANKING_API_URL_TPL = "https://www.robotevents.com/api/seasons/125/skills?post_season=0&grade_level=%s";
    const TEAM_PROFILE_URL_TPL = 'https://www.robotevents.com/teams/VRC/%s';
    const EVENT_URL_TPL = 'https://www.robotevents.com/robot-competitions/vex-robotics-competition/%s.html';
    const EVENT_LIST_JSON_URL = "https://www.robotevents.com/robot-competitions/vex-robotics-competition/table?seasonId=125&eventType=1&name=&from_date=01%2F01%2F2019&to_date=&country_id=244&grade_level_id=2&country_region_id=12&city=&level_class_id=&draw=1&columns%5B0%5D%5Bdata%5D=status&columns%5B0%5D%5Bname%5D=status&columns%5B0%5D%5Bsearchable%5D=true&columns%5B0%5D%5Borderable%5D=false&columns%5B0%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B0%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B1%5D%5Bdata%5D=spots_open&columns%5B1%5D%5Bname%5D=spots_open&columns%5B1%5D%5Bsearchable%5D=true&columns%5B1%5D%5Borderable%5D=false&columns%5B1%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B1%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B2%5D%5Bdata%5D=event_code&columns%5B2%5D%5Bname%5D=sku&columns%5B2%5D%5Bsearchable%5D=true&columns%5B2%5D%5Borderable%5D=false&columns%5B2%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B2%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B3%5D%5Bdata%5D=event_date&columns%5B3%5D%5Bname%5D=event_start_date&columns%5B3%5D%5Bsearchable%5D=true&columns%5B3%5D%5Borderable%5D=false&columns%5B3%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B3%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B4%5D%5Bdata%5D=location&columns%5B4%5D%5Bname%5D=venues.city&columns%5B4%5D%5Bsearchable%5D=true&columns%5B4%5D%5Borderable%5D=false&columns%5B4%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B4%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B5%5D%5Bdata%5D=event_type.name&columns%5B5%5D%5Bname%5D=eventType.name&columns%5B5%5D%5Bsearchable%5D=true&columns%5B5%5D%5Borderable%5D=false&columns%5B5%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B5%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B6%5D%5Bdata%5D=name_link&columns%5B6%5D%5Bname%5D=name&columns%5B6%5D%5Bsearchable%5D=true&columns%5B6%5D%5Borderable%5D=false&columns%5B6%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B6%5D%5Bsearch%5D%5Bregex%5D=false&order%5B0%5D%5Bcolumn%5D=0&order%5B0%5D%5Bdir%5D=asc&start=0&length=50&search%5Bvalue%5D=&search%5Bregex%5D=false&_=1547715441474";
    public function index(){
        $events = self::parseEventListFromJsonUrl(static::EVENT_LIST_JSON_URL, null);
        return $this->render('vexvrc.index.html.twig', ['events' => $events]);
    }

}
