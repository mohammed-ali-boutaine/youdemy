
<?php
function tagHandler($content)
{

    // content example
    // $string = "hello this is a post about #coding and #programming";
    $tags = [];
    $words = explode(" ", $content);
    for ($i = 0; $i < sizeof($words); $i++) {
        $word = $words[$i];

        if ($words[$i][0] === "#" && strlen($word) > 1) {

            $tag = substr(strtolower($words[$i]), 1);

            if (!in_array($tag, $tags)) {
                $tags[] = $tag;
            }
            $words[$i] = "<a href='page#$tag'>" . $words[$i] . "</a>";
        }
    }

    $content = implode(" ", $words);
    
    $result = [ "tags" => $tags , "content" => $content] ;

    return $result;

}