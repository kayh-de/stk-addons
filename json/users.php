<?php
/**
 * copyright 2014 Daniel Butum <danibutum at gmail dot com>
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

if (!isset($_POST["action"]))
{
    exit_json_error("action param is not defined or is empty");
}

if (!User::isLoggedIn())
{
    exit_json_error("You are not a logged in");
}

switch ($_POST["action"])
{
    case "edit-profile":
        $errors = Validate::ensureInput($_POST, ["user-id"]);
        if ($errors)
        {
            exit_json_error(_h("User id  is empty"));
        }

        $user_id = (int)$_POST["user-id"];
        $homepage = isset($_POST["homepage"]) ? $_POST["homepage"] : "";
        $real_name = isset($_POST["realname"]) ? $_POST["realname"] : "";

        try
        {
            User::updateProfile($user_id, $homepage, $real_name);
        }
        catch(UserException $e)
        {
            exit_json_error($e->getMessage());
        }

        exit_json_success(_h("Profile updated"));
        break;

    case "edit-role":
        $errors = Validate::ensureInput($_POST, ["role", "user-id"]);
        if ($errors)
        {
            exit_json_error(_h("Role field is empty"));
        }

        $user_id = (int)$_POST["user-id"];
        $role = $_POST["role"];
        $available = isset($_POST["available"]) ? $_POST["available"] : "";

        try
        {
            User::updateRole($user_id, $role, $available);
        }
        catch(UserException $e)
        {
            exit_json_error($e->getMessage());
        }

        exit_json_success(_h("Role edited successfully"));
        break;

    case "change-password":
        $errors = Validate::ensureInput($_POST, ["old-pass", "new-pass", "new-pass-verify"]);
        if ($errors)
        {
            exit_json_error(_h("One or more fields are empty"));
        }

        try
        {
            User::verifyAndChangePassword($_POST["old-pass"], $_POST["new-pass"], $_POST["new-pass-verify"], User::getLoggedId());
        }
        catch(UserException $e)
        {
            exit_json_error($e->getMessage());
        }

        exit_json_success(_h("Your password has been changed"));
        break;

    case "remove-friend": // remove friend
        $errors = Validate::ensureInput($_POST, ["friend-id"]);
        if ($errors)
        {
            exit_json_error(_h("Friend id is empty"));
        }

        try
        {
            Friend::removeFriend(User::getLoggedId(), $_POST["friend-id"]);
        }
        catch(FriendException $e)
        {
            exit_json_error($e->getMessage());
        }

        exit_json_success(_h("Friend removed"));
        break;

    case "accept-friend": // accept friend request
        $errors = Validate::ensureInput($_POST, ["friend-id"]);
        if ($errors)
        {
            exit_json_error(_h("Friend id is empty"));
        }

        try
        {
            Friend::acceptFriendRequest($_POST["friend-id"], User::getLoggedId());
        }
        catch(FriendException $e)
        {
            exit_json_error($e->getMessage());
        }

        exit_json_success(_h("Friend request accepted"));
        break;

    case "decline-friend": // decline friend request
        $errors = Validate::ensureInput($_POST, ["friend-id"]);
        if ($errors)
        {
            exit_json_error(_h("Friend id is empty"));
        }

        try
        {
            Friend::declineFriendRequest($_POST["friend-id"], User::getLoggedId());
        }
        catch(FriendException $e)
        {
            exit_json_error($e->getMessage());
        }

        exit_json_success(_h("Friend request declined"));
        break;

    case "cancel-friend": // cancel a friend request
        $errors = Validate::ensureInput($_POST, ["friend-id"]);
        if ($errors)
        {
            exit_json_error(_h("Friend id is empty"));
        }

        try
        {
            Friend::cancelFriendRequest(User::getLoggedId(), $_POST["friend-id"]);
        }
        catch(FriendException $e)
        {
            exit_json_error($e->getMessage());
        }

        exit_json_success(_h("Friend request canceled"));
        break;

    default:
        exit_json_error(sprintf("action = %s is not recognized", h($_POST["action"])));
        break;
}