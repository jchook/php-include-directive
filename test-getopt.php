<?php

$rest_index = 0;

print_r(getopt('o:', [], $rest_index));
print_r(array_slice($argv, $rest_index));


