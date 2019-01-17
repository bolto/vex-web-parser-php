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
}
