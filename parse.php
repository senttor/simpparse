<?php
define("PERBLOCK", 10);
require_once "simple_html_dom.php";
//include('orm.class.php');
include_once "db2.class.php";

$url = "";


if (isset($argv[1])) {
    $action = $argv[1];
    echo "action   is : ".$action.PHP_EOL;
    if ( $action =='catalog' && function_exists("getCatalogLink")) {
        getCatalogLink($url);
    } else if($action == "articles" && function_exists("getArticledata")) {
        //для нескольких потоков
       /* while (true){
            $uniq = md5(uniqid().time());
            $query = "update artciles set uniq=? where uniq='NULL' limit ?";
           $smpt = DB::prepare($query);
            $smpt->execute([$uniq, PERBLOCK]);

            $articles = DB::run("select url from artciles where uniq=?",[$uniq])->fetchAll(PDO::FETCH_KEY_PAIR);
            if(!$articles) {
                exit();
            }
        }
        foreach ($articles as $article) {
            getArticledata($article);
        }*/

        while ($article = DB::run("select url from artciles where date_parsed='NULL' limit 1")->fetchColumn()){
            //echo $sss.PHP_EOL;
            getArticledata($article);
            die();
        }
    } else {
        echo "Not such action".PHP_EOL;
        exit();
    }
        //echo "function {$action} can`t be called".PHP_EOL;
}else{
    echo "Please, input one parametr".PHP_EOL;;
    exit();
}

/**
 * @param $url
 * @return mixed
 * находит в статье все заголовки h1 и
 * вытягивает контент
 */
function getArticledata($linkOnPage) {


    //echo PHP_EOL.$url.PHP_EOL;

    $article = file_get_html($linkOnPage);

    $h1 = $article->find('h1',0)->innertext;
    $content = $article->find('article',0)->innertext;
  //  echo PHP_EOL.$h1.PHP_EOL;
 //   $data = compact('h1','content');

    $sql_query = "update artciles set h1=?, content=?, date_parsed = NOW() where url=?";
  //  echo PHP_EOL.$sql_query.PHP_EOL;
    $smpt = DB::prepare($sql_query);

    $smpt->execute([$h1,$content,$linkOnPage]);

    //   return $data;
}

/**
 * @param $url
 *
 * собирает ссылки с сайта по заданому селектору
 */

function getCatalogLink($url)
{
    $html = file_get_html($url);


    foreach ($html->find('a.read-more-link') as $linkOnPage) {
        print $linkOnPage->href . PHP_EOL;
        //  print getArticledata($link->href);

        $sql_query = "insert ignore into artciles set url = ";
        $smpt = DB::prepare($sql_query."?");
        $smpt->execute([$linkOnPage->href]);
     // сохранение статьи
    //    getArticledata($linkOnPage->href);
        die();
    }

// переход по страницам
    if($next_page = $html->find('a.next', 0)) {
        getCatalogLink($next_page->href);
    }


}





