<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Tags;
use App\Models\Notif;

class HomeController extends Controller
{
    public function index($request, $response)
    {
        $user_list['tags_list'] = Tags::getTagsList($this->container['conn']);
        $user_list['who'] = $_SESSION['user'];
        $user_info = User::getUserByOneParam('id', $_SESSION['user'], $this->container['conn']);
        $user_list['user'] = array();

        if ($user_info)
            $user_list['users'] = User::getUsersList($user_info, $this->container['conn']);

        if ($user_list['users']) {
            foreach ($user_list['users'] as $key => $item) {
                $user_list['users'][$key]['interest'] = $item['rating'];
                if (User::userIsBlockedOrBlockMe($_SESSION['user'], $item['id'], $this->container['conn'])) {
                 unset($user_list['users'][$key]);
                 continue;
                }
                if ($item['distance'] <= 10)
                    $user_list['users'][$key]['interest'] += 500;
                else if ($item['distance'] <= 50)
                    $user_list['users'][$key]['interest'] += 400;
                else if ($item['distance'] <= 100)
                    $user_list['users'][$key]['interest'] += 300;
                else if ($item['distance'] <= 150)
                    $user_list['users'][$key]['interest'] += 200;
                else if ($item['distance'] <= 200)
                    $user_list['users'][$key]['interest'] += 100;
                else if ($item['distance'] <= 250)
                    $user_list['users'][$key]['interest'] += 50;

                $search_tags = explode(',', $item['tags']);
                $user_tags = explode(',', $user_info['tags']);
                $user_list['users'][$key]['interest'] += count(array_intersect($search_tags, $user_tags)) * 25;
                $user_list['users'][$key]['identical_tags'] = count(array_intersect($search_tags, $user_tags));
                $user_list['users'][$key]['tags'] = $search_tags;
            }
            usort($user_list['users'], function ($a, $b) {
                if ($a['interest'] == $b['interest'])
                    return 0;
                return ($a['interest'] < $b['interest']) ? 1 : -1;
            });
        }
        return $this->container->view->render($response, 'home.twig', $user_list);
    }

    public function getntf($request, $response) {
        $data = Notif::getNew($this->container['conn']);

        $response = $response->withJson( $data );
        return $response;
    }

    public function map($request, $response) {
        $data['who'] = $_SESSION['user'];
        return $this->container->view->render($response, 'map.twig', $data);
    }
    public function getmap($request, $response) {
        $data = User::getUserListForMap($this->container['conn']);

        $response = $response->withJson( $data );
        return $response;
//        return $this->container->view->render($response, 'map.twig', $data);
    }

}
