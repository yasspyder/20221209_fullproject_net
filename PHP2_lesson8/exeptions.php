<?php

class MyErrorException extends Exception {}
class HttpErrorException extends Exception {}

function foo()
{

    try {

        throw new MyErrorException("My error ");
    } catch (MyErrorException $exception) {
        echo "error 1 \n";
        echo $exception->getMessage();
        return false;
    }
    return true;
}

try {
    echo "start\n";
    var_dump(foo());
    echo "end\n";
} catch (HttpErrorException $exception) {
    echo "error 2 \n";
    echo $exception->getMessage();
} catch (Exception $exception) {
    echo "Все ошибки \n";
    echo $exception->getMessage();
}
