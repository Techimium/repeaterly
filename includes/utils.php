<?php

namespace Repeaterly\Includes;

class Utils {
    public static function trim_words(string $text, int $max_length = 20, string $more = '...') {
        $words = explode(' ', wp_strip_all_tags($text));
        
        if (count($words) <= $max_length) {
            return implode(' ', $words);
        }

        $trimmed = array_slice($words, 0, $max_length);
        
        return implode(' ', $trimmed) . $more;
    }
}
