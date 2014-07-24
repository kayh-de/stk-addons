<?php
/**
 * copyright 2011 computerfreak97
 *           2014 Daniel Butum <danibutum at gmail dot com>
 *
 * This file is part of stkaddons
 *
 * stkaddons is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * stkaddons is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with stkaddons.  If not, see <http://www.gnu.org/licenses/>.
 */
require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . "config.php");
AccessControl::setLevel(AccessControl::PERM_VIEW_BASIC_PAGE);

if (empty($_GET['addon-id']))
{
    exit_json_error('No addon id provided');
}

if (empty($_GET["action"]))
{
    exit_json_error("action param is not defined or is empty");
}

if (!Addon::exists($_GET['addon-id']))
{
    exit_json_error('The addon does not exist ' . h($_GET['addon-id']));
}

$rating = new Rating($_GET['addon-id']);
$numRatingsString = 0;

function getOverallRating(Rating $rating)
{
    // update star ratings
    if ($rating->getNumRatings() === 1)
    {
        return $rating->getNumRatings() . ' Vote';
    }

    return $rating->getNumRatings() . ' Votes';
}

switch ($_GET['action'])
{
    case "set": // set rating and get the overall rating
        if (empty($_GET["rating"]))
        {
            exit_json_error("rating param is not defined or is empty");
        }

        // set rating
        try
        {
            $rating->setUserVote($_GET['rating']);
        }
        catch(RatingsException $e)
        {
            exit_json_error($e->getMessage());
        }

        exit_json_success("Rating set", ["width" => $rating->getAvgRatingPercent(), "num-ratings" => getOverallRating($rating)]);
        break;

    case "get": // get overall rating
        exit_json_success("", ["width" => $rating->getAvgRatingPercent(), "num-ratings" => getOverallRating($rating)]);
        break;

    default:
        exit_json_error(sprintf("action = %s is not recognized", h($_GET["action"])));
        break;
}
