<?php
/*
Plugin Name: UTC Webtv2
Description: Specific helper functions for the theme webtv2
Author: Sylvain Tognola
ClipBucket Version: 2
Version: 1.0
Plugin Type: global

*/
/*

          {*{$vdo.category|@var_dump} {$vdo|@var_dump}*}
          
          {foreach from=$comcategories item=ncat}
            {$ccat=$cbvid->get_cat_by_name($ncat)} 
            {$ccatid=$ccat['category_id']}
            {"/#"+$ccatid+"#/"}
            {if preg_match("/#"+$ccatid+"#/",$vdo.category)}
                {$ncat}
            {/if}
          {/foreach}
          TODO {prout}</span></div></div>
          */
          
if(!function_exists('utc_helper_function'))
{
    function utc_helper_function(){}
    
    $comcategories = array(
        $cbvid->get_cat_by_name("Vie de l’Université"),
        $cbvid->get_cat_by_name("L’innovation"),
        $cbvid->get_cat_by_name("L’UTC à l’international"),
        $cbvid->get_cat_by_name("Portraits d’utécéens"),
        $cbvid->get_cat_by_name("Les grands axes de recherche"),
        $cbvid->get_cat_by_name("La formation"),
        $cbvid->get_cat_by_name("Remise des diplômes et parrains"),
        $cbvid->get_cat_by_name("Missions et valeurs de l’UTC"),
        $cbvid->get_cat_by_name("Ces thèses qui changent la vie")
    );
    
    function getcomcategory ($params) {
        global $comcategories;
        extract($params);
        foreach($comcategories as $cat){
            if(preg_match("/#".$cat["category_id"]."#/", $vid["category"])){
                echo $cat["category_name"];
                return;
            }
        }
        echo "UTC";
    }
        
    function prout(){
        echo "prout!";
    }
    
    
    $Smarty->assign('utc_comcategories', $comcategories);
    
    $Smarty->register_function('utc_getcomcategory', 'getcomcategory');
    $Smarty->register_function('utc_prout', 'prout');
    /*
    $smarty->register_function('date_now', 'print_current_date');

    function print_current_date ($params) {
        extract($params);
        if(empty($format))
            $format="%b %e, %Y";
        echo strftime($format,time());
    }
    */

}

