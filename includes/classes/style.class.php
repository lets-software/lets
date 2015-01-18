<?php


class style {

    var
    $style_id,
    $dynamic_elements,
    $print_page,
    $set;

    function style() {
        $this->set = 0;
        $this->dynamic_elements = '';
        $print = return_link_variable('print','');
        $this->print_page = $print;
    }
    
    function exists($style_id) {
        if (empty($style_id)) {
            return false;
        }
        $query = 'SELECT * FROM style WHERE styleID = '.$style_id;
        $result = mysql_query($query);
        if (!$result) {
            return false;
        }
        if (mysql_num_rows($result) == 0) {
            return false;
        }
        define('TEXT_COLOUR',mysql_result($result,0,'text_colour'));
        define('FONT',mysql_result($result,0,'font'));
        define('BACKGROUND_COLOUR',mysql_result($result,0,'background_colour'));
        define('HEADER_COLOUR',mysql_result($result,0,'header_colour'));
        define('HEADER_BORDER_COLOUR',mysql_result($result,0,'header_border_colour'));
        define('HEADER_BORDER_SIZE',mysql_result($result,0,'header_border_size'));
        define('TAB_COLOUR',mysql_result($result,0,'tab_colour'));
        define('TAB_BORDER_COLOUR',mysql_result($result,0,'tab_border_colour'));
        define('TAB_BORDER_SIZE',mysql_result($result,0,'tab_border_size'));
        define('FONT_SIZE',mysql_result($result,0,'font_size'));
        define('TEXT_BACKGROUND_COLOUR',mysql_result($result,0,'text_background_colour'));
        define('LINK_COLOUR',mysql_result($result,0,'link_colour'));
        define('LINK_DECORATION',mysql_result($result,0,'link_decoration'));
        define('VISITED_COLOUR',mysql_result($result,0,'visited_colour'));
        define('VISITED_DECORATION',mysql_result($result,0,'visited_decoration'));
        define('HOVER_COLOUR',mysql_result($result,0,'hover_colour'));
        define('HOVER_DECORATION',mysql_result($result,0,'hover_decoration'));
        define('HEADER_HTML',mysql_result($result,0,'header'));
        define('REQUIRED_COLOUR',mysql_result($result,0,'required_color'));
        define('REQUIRED_TEXT_DECORATION',mysql_result($result,0,'required_text_decoration'));
        define('REQUIRED_FONT_WEIGHT',mysql_result($result,0,'required_font_weight'));
        define('REQUIRED_DISPLAY',mysql_result($result,0,'required_display'));
        $this->style_id = $style_id;
        $this->set = 1;
        return true;
    }
    
    function style_header() {
        if (!$this->print_page) {
            $styles = '<link rel="stylesheet" href="'.URL.'templates/'.TEMPLATE."/styles/main.css\" /> \n";
            $styles .= "<style type=\"text/css\">\n";
            
            return $styles;
        }
    }
    function style_footer() {
        if ($this->print_page) {
            return '<link rel="stylesheet" type="text/css" href="'.URL.'templates/'.TEMPLATE.'/styles/print.css" title="print_page" />'."\n";        
        } else {
            // $styles = " @import url(".URL.'templates/'.TEMPLATE."/styles/main.css);\n";
             $styles = " body { color: ".TEXT_COLOUR."; font-family: ".FONT."; font-size: ".FONT_SIZE."px; background-color: ".BACKGROUND_COLOUR."; }\n";
            $styles .= " h1 { font-size: ".(FONT_SIZE + 16)."px; font-weight: normal; font-style:italic}\n";
            $styles .= " h2 { font-size: ".(FONT_SIZE + 12)."px; font-weight: normal; font-style:italic}\n";
            
            if ($GLOBALS['min_width'] != $GLOBALS['default_min_width']) {
                $styles .= " #container { width:".$GLOBALS['min_width']."px; }\n";
                $styles .= " #header { width:".$GLOBALS['min_width']."px; background-color: ".HEADER_COLOUR."; border-bottom: ".HEADER_BORDER_SIZE."px solid ".HEADER_BORDER_COLOUR.";}\n";
            } else {
                $styles .= " #container { min-width:".$GLOBALS['min_width']."px; }\n";
                //$styles .= " #header { background-color: ".HEADER_COLOUR."; border-bottom: ".HEADER_BORDER_SIZE."px solid ".HEADER_BORDER_COLOUR.";}\n";
                $styles .= " #header { background-color: ".HEADER_COLOUR."; }\n";
            }
            $styles .= " #tab { background-color: ".TAB_COLOUR."; }\n";
            $styles .= " #navigation { font-size: ".(FONT_SIZE + 2)."px; }\n";
            // $styles .= " #main { background-color: ".TEXT_BACKGROUND_COLOUR."; border-left: ".TAB_BORDER_SIZE."px solid ".TAB_BORDER_COLOUR."; border-right: ".TAB_BORDER_SIZE."px solid ".TAB_BORDER_COLOUR."; border-bottom: ".TAB_BORDER_SIZE."px solid ".TAB_BORDER_COLOUR.";}\n";
            $styles .= " #main { background-color: ".TEXT_BACKGROUND_COLOUR."; }\n";
            $styles .= " #holder { background-color: ".TEXT_BACKGROUND_COLOUR."; }\n";
            $styles .= " a:link { color: ".LINK_COLOUR."; text-decoration: ".LINK_DECORATION."; }\n";
            $styles .= " a:visited { color: ".VISITED_COLOUR."; text-decoration: ".VISITED_DECORATION."; }\n";
            $styles .= " a:hover { color: ".HOVER_COLOUR."; text-decoration: ".HOVER_DECORATION."; }\n";
            $styles .= " a.nav_link_selected { color: ".HOVER_COLOUR."; text-decoration: ".HOVER_DECORATION."; }\n";
            $styles .= " span.nav_links_heading { font-size: ".(FONT_SIZE + 3)."px; color: ".HOVER_COLOUR."; }\n";
            $styles .= " span.sidebar_heading { font-size: ".(FONT_SIZE + 3)."px; color: ".HOVER_COLOUR."; }\n";
            $styles .= " span.required_field { font-weight: ".REQUIRED_FONT_WEIGHT."; text_decoration: ".REQUIRED_TEXT_DECORATION."; color: ".REQUIRED_COLOUR."; }\n";
            $styles .= " fieldset { padding:5px; border:1px solid ".TAB_COLOUR.";}\n";
            $styles .= " legend { border:1px solid ".TAB_COLOUR."; color:".HOVER_COLOUR."; }\n";
            $styles .= " input,label,textarea,select { font-size: ".(FONT_SIZE - 1)."px; }\n";
            $styles .= " div.article_image_holder {float:left; width:".IMAGE_WIDTH_THUMB_ARTICLE."px; margin-right:5px; }\n";
            $styles .= " #article_page_image_holder {float:left; width:".IMAGE_WIDTH_PAGE_ARTICLE."px; }\n";
            
            if (TEMPLATE == 'viclets') {
                $styles .= " #tableborder_left { background-color: ".HEADER_COLOUR."; border-left: ".HEADER_BORDER_SIZE."px solid ".HEADER_BORDER_COLOUR."; border-bottom: ".HEADER_BORDER_SIZE."px solid ".HEADER_BORDER_COLOUR."; border-top: ".HEADER_BORDER_SIZE."px solid ".HEADER_BORDER_COLOUR.";}\n";
                $styles .= " #tableborder_right { background-color: ".HEADER_COLOUR."; border-right: ".HEADER_BORDER_SIZE."px solid ".HEADER_BORDER_COLOUR."; border-bottom: ".HEADER_BORDER_SIZE."px solid ".HEADER_BORDER_COLOUR."; border-top: ".HEADER_BORDER_SIZE."px solid ".HEADER_BORDER_COLOUR."; }\n";
                $styles .= " #sidebox { background: ".TAB_COLOUR."; border: ".TAB_BORDER_SIZE."px solid ".TAB_BORDER_COLOUR."}\n";    
            }
            
            if (!empty($this->dynamic_elements)) {
                $styles .= $this->dynamic_elements;
            }
            
            $styles .= "</style>\n";
            return $styles;
        }
    }
}


?>