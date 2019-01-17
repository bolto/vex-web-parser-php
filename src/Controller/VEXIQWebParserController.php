<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * JSON API URL for getting event list
 * https://www.robotevents.com/robot-competitions/vex-iq-challenge/table?seasonId=&eventType=1&name=&from_date=01%2F12%2F2019&to_date=&country_id=244&grade_level_id=2&country_region_id=12&city=&level_class_id=&draw=1&columns%5B0%5D%5Bdata%5D=status&columns%5B0%5D%5Bname%5D=status&columns%5B0%5D%5Bsearchable%5D=true&columns%5B0%5D%5Borderable%5D=false&columns%5B0%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B0%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B1%5D%5Bdata%5D=spots_open&columns%5B1%5D%5Bname%5D=spots_open&columns%5B1%5D%5Bsearchable%5D=true&columns%5B1%5D%5Borderable%5D=false&columns%5B1%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B1%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B2%5D%5Bdata%5D=event_code&columns%5B2%5D%5Bname%5D=sku&columns%5B2%5D%5Bsearchable%5D=true&columns%5B2%5D%5Borderable%5D=false&columns%5B2%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B2%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B3%5D%5Bdata%5D=event_date&columns%5B3%5D%5Bname%5D=event_start_date&columns%5B3%5D%5Bsearchable%5D=true&columns%5B3%5D%5Borderable%5D=false&columns%5B3%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B3%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B4%5D%5Bdata%5D=location&columns%5B4%5D%5Bname%5D=venues.city&columns%5B4%5D%5Bsearchable%5D=true&columns%5B4%5D%5Borderable%5D=false&columns%5B4%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B4%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B5%5D%5Bdata%5D=event_type.name&columns%5B5%5D%5Bname%5D=eventType.name&columns%5B5%5D%5Bsearchable%5D=true&columns%5B5%5D%5Borderable%5D=false&columns%5B5%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B5%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B6%5D%5Bdata%5D=name_link&columns%5B6%5D%5Bname%5D=name&columns%5B6%5D%5Bsearchable%5D=true&columns%5B6%5D%5Borderable%5D=false&columns%5B6%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B6%5D%5Bsearch%5D%5Bregex%5D=false&order%5B0%5D%5Bcolumn%5D=0&order%5B0%5D%5Bdir%5D=asc&start=0&length=25&search%5Bvalue%5D=&search%5Bregex%5D=false&_=1547344903801
 */

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Sunra\PhpSimple\HtmlDomParser;

class VEXIQWebParserController {

    // VEX IQ school levels: elementary, middle school
    const MIDDLE_SCHOOL = 'Middle School';
    const ELEMENTARY_SCHOOL = 'Elementary';
    const SCHOOL_LEVELS = array(VEXIQWebParserController::MIDDLE_SCHOOL, VEXIQWebParserController::ELEMENTARY_SCHOOL);

    var $worldRankingsJsonObjects;

    public function getWorldRankingBySchoolLevel($schoolLevel) {
        if ($schoolLevel == null) {
            $error = '$shoolLevel can not be null.';
            throw new Exception($error);
        }

        if (!in_array($schoolLevel, VEXIQWebParserController::SCHOOL_LEVELS)) {
            $error = '$shoolLevel must be one of the following: ' . implode(", ", VEXIQWebParserController::SCHOOL_LEVELS);
            $error .= ".  Supplied value: " . $schoolLevel;
            throw new Exception($error);
        }

        if ($this->worldRankingsJsonObjects == null) {
            $this->worldRankingsJsonObjects = array();
            $worldRankingsUrls = array();
            foreach (VEXIQWebParserController::SCHOOL_LEVELS as $level) {
                $worldRankingJsonApiUrl = sprintf("https://www.robotevents.com/api/seasons/124/skills?post_season=0&grade_level=%s", $level);
                $worldRankingJsonApiUrl = str_replace(" ", "%20", $worldRankingJsonApiUrl);
                $worldRankingsUrls[$level] = $worldRankingJsonApiUrl;
            }
            $worldRankingJsonApiContents = $this->getMultipleWebRequests($worldRankingsUrls);
            foreach ($worldRankingJsonApiContents as $level => $apiContent) {
                $json_a = json_decode($apiContent, true);
                $rankings = array();
                foreach ($json_a as $ranking) {
                    $rankings[$ranking["team"]["team"]] = $ranking;
                }
                $this->worldRankingsJsonObjects[$level] = $rankings;
            }
        }
        return $this->worldRankingsJsonObjects[$schoolLevel];
    }

    public function getTeamWorldRankingBySchoolLevel($teamNumber, $schoolLevel) {
        $res = null;
        $rankings = $this->getWorldRankingBySchoolLevel($schoolLevel);
        if (isset($rankings[$teamNumber])) {
            $res = $rankings[$teamNumber];
        }
        return $res;
    }

    public function getTeamWorldRanking($teamNumber) {
        $res = null;
        foreach (VEXIQWebParserController::SCHOOL_LEVELS as $schoolLevel) {
            $res = $this->getTeamWorldRankingBySchoolLevel($teamNumber, $schoolLevel);
            if ($res != null)
                break;
        }
        return $res;
    }

    public function getWorldRankingHtml($teamNumber) {
        $ranking = $this->getTeamWorldRanking($teamNumber);
        $res = "";
        if ($ranking == null) {
            $res = sprintf("%s was not found in world rankings in the following school levels: %s", $teamNumber, implode(", ", VEXIQWebParserController::SCHOOL_LEVELS));
        } else {
            $scores = $ranking["scores"];
            $res = sprintf("World ranking: %s, score: %s, programming: %s, driver: %s, maxProgramming: %s, maxDriver: %s",
                    $ranking["rank"], $scores["score"], $scores["programming"], $scores["driver"], $scores["maxProgramming"], $scores["maxDriver"]);
        }
        return $res . "<br>";
    }

    public function getTeamApiNumber($teamNumber) {
        /**
         * Each canonical team number is the external team number. From VEX's
         * database, there is a real database team id, which can be found in
         * team's profile web page.
         *
         * Example:
         *     <profile :team="93571" team-number="3716Z">
         *
         * Note: canonical team number is 3716Z, but database team id is 93571
         */
        $url = sprintf('https://www.robotevents.com/teams/VIQC/%s', $teamNumber);
        $html = file_get_contents($url);
        $pattern = "/:team=\"[0-9]+\"/";
        $teamApiNumber = null;
        $success = preg_match($pattern, $html, $match);
        if ($success) {
            $teamApiNumber = str_replace("\"", "", str_replace(":team=", "", $match[0]));
        }
        return $teamApiNumber;
    }

    public function getTeamProfileJsonObject($teamNumber) {
        $teamApiNumber = $this->getTeamApiNumber($teamNumber);
        $string = file_get_contents(sprintf("https://www.robotevents.com/api/teams/%s/awards", $teamApiNumber));
        $json_a = json_decode($string, true);
        return $json_a;
    }

    public function getTeamAwards($teamNumber) {
        $teamApiNumber = $this->getTeamApiNumber($teamNumber);
        $string = file_get_contents(sprintf("https://www.robotevents.com/api/teams/%s/awards", $teamApiNumber));
        $json_a = json_decode($string, true);

        $events = $json_a["data"];

        $res = "";
        foreach ($events as $event) {
            $eventName = $event["name"];
            $res .= "&nbsp;&nbsp;&nbsp;&nbsp;" . $eventName . '<br>';
            foreach ($event["awards"] as $award) {
                $awardName = $award["name"];
                $res .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $awardName . "<br>";
            }
        }
        return $res;
    }

    public function getTeamAwardsHtml($teamNumber, $awardsJsonObject) {
        $events = $awardsJsonObject["data"];

        $res = "";
        foreach ($events as $event) {
            if ($event["end_at"] < 1525942221000)
                continue;
            $eventName = $event["name"];
            $res .= "<ul class=\"list-unstyled\">\n";
            $res .= sprintf("<li>%s", $eventName);
            $res .= "<ul>\n";
            foreach ($event["awards"] as $award) {
                $awardName = $award["name"];
                $res .= "<li>" . $awardName . "</li>";
            }
            $res .= "</ul></li></ul>\n";
        }
        return $res;
    }

    public function getMultipleWebRequestsInGroupsOfTen($urls, $groupSize = 40) {
        $counter = 0;
        $batchArray = null;
        $allResponses = array();
        foreach ($urls as $name => $value) {
            if (($counter % $groupSize) == 0) {
                $batchArray = array();
            }
            $batchArray[$name] = $value;
            $counter ++;
            if (($counter % $groupSize) == 0 || $counter == sizeof($urls)) {
                $responses = $this->getMultipleWebRequests($batchArray);
                $allResponses = array_merge($allResponses, $responses);
            }
        }
        return $allResponses;
    }

    public function getMultipleWebRequests($urls) {
        /**
         * Return an array of web results
         * @param type $urls, list of team numbers and their urls.
         *                    eg: ["team1" => "some_url"]
         * @return Response
         */
        $curlCalls = array();
        //create the multiple cURL handle
        $mh = curl_multi_init();
        foreach ($urls as $teamNumber => $url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            curl_multi_add_handle($mh, $ch);
            $curlCalls[$teamNumber] = $ch;
        }

        $active = null;
        //execute the handles
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc === CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc === CURLM_OK) {
            $mrc = curl_multi_exec($mh, $active);
            if (curl_multi_select($mh) == -1) {
                continue;
            }
        }
        $responses = array();
        foreach ($curlCalls as $teamNumber => $curlCall) {
            // clean up handles
            $responses[$teamNumber] = curl_multi_getcontent($curlCall);
            curl_multi_remove_handle($mh, $curlCall);
        }
        curl_multi_close($mh);
        return $responses;
    }

    public function parseTeamListFromEvent($name) {
        $url = sprintf('https://www.robotevents.com/robot-competitions/vex-iq-challenge/%s.html', $name);
        $html = file_get_contents($url);

        // try loading the dom
        try {
            $instance = new HtmlDomParser();
            $dom = $instance->str_get_html($html);
        } catch (\SimpleHtmlDomException $e) {
            // do something
            echo $e;
        }
        $eventNameXpath = "//*[@id=\"front-app\"]/div[1]/div[1]/h3";
        $eventName = trim($dom->find($eventNameXpath)[0]->innertext);
        $eventDateXpath = "//*[@id=\"front-app\"]/div[1]/div[1]/span";
        $eventDate = trim($dom->find($eventDateXpath)[0]->innertext);
        $res = sprintf("%s (%s) Teams and Award History:<br>", $eventName, $eventDate);
        $tableRowXpath = '//div[id=teamList]/table/tbody/tr';
        $counter = 0;
        $teamNumberList = array();
        $teamProfileUrls = array();
        foreach ($dom->find($tableRowXpath) as $element) {
            $start_date = new \DateTime();
            $counter ++;
            // first row is table header, skipping first row
            if ($counter == 1)
                continue;
            // <tr>'s <td> has a <a> tag, which is team profile link
            $team_link = $element->children(0)->children(0);
            $teamNumber = $team_link->innertext;
            $teamNameElement = $element->children(1);
            $teamName = $teamNameElement->innertext;
            $teamNumberList[$teamNumber] = $teamName;
            $teamProfileUrls[$teamNumber] = sprintf('https://www.robotevents.com/teams/VIQC/%s', $teamNumber);
        }

        $teamProfileHtmlContents = $this->getMultipleWebRequestsInGroupsOfTen($teamProfileUrls);
        $teamAwardsApiUrls = array();
        foreach ($teamProfileHtmlContents as $teamNumber => $teamProfileHtml) {
            if ($teamProfileHtml == null)
                throw \Exception("$teamProfileHtml is null");
            $pattern = "/:team=\"[0-9]+\"/";
            $teamApiNumber = null;
            $success = preg_match($pattern, $teamProfileHtml, $match);
            if ($success) {
                $teamApiNumber = str_replace("\"", "", str_replace(":team=", "", $match[0]));
            } else {
                throw \Exception("team api number not found.");
            }
            $teamAwardsApiUrl = sprintf("https://www.robotevents.com/api/teams/%s/awards", $teamApiNumber);
            $teamAwardsApiUrls[$teamNumber] = $teamAwardsApiUrl;
        }
        $teamAwardsJsonContents = $this->getMultipleWebRequestsInGroupsOfTen($teamAwardsApiUrls);

        foreach ($teamAwardsJsonContents as $teamNumber => $teamAwardsJsonContent) {
            $teamAwardsJsonObject = json_decode($teamAwardsJsonContent, true);
            $awardsResult = $this->getTeamAwardsHtml($teamNumber, $teamAwardsJsonObject);
            if ($awardsResult == "") {
                $awardsResult = "No awards found";
            }
            $worldRanking = $this->getWorldRankingHtml($teamNumber);
            $teamStatsHtml = sprintf(
                    "<ul>"
                    . "<li>%s (%s)"
                    . "  <ul>"
                    . "    <li>World Ranking"
                    . "      <ul>"
                    . "        <li>%s</li>"
                    . "      </ul>"
                    . "    </li>"
                    . "    <li>Awards"
                    . "      %s"
                    . "    </li>"
                    . "  </ul>"
                    . "</li>"
                    . "</ul>", $teamNumberList[$teamNumber], $teamNumber, $worldRanking, $awardsResult);
            $res .= $teamStatsHtml;
        }
        return new Response('<html><body>' . $res . '</body></html>');
    }

}
