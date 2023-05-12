<?php

namespace Test;

trait UUIDGen
{
    private function genFakeUUID(int $input):string {
        $str = (string)$input;
        $str = str_repeat($str, 35);
        $result = substr($str,0,35);
        $result[8] = '-';
        $result[13] = '-';
        $result[18] = '-';
        $result[23] = '-';
        return $result;
    }

}
