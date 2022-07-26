<?php

/**
 * Para restringir la edad a menores de 18 años he añadido el siguiente código sobreescribiendo parte del archivo padre hallado en classes/Validate.php.
 * La función getLastErrors() funciona como filtro para commprobar que la fecha se ha introducido correctamente en terminos generales.
 * Validate::isBirthDate($date) == false comprueba que la edad introducida es mayor a 18 años y si no lo es arroja una alerta indicando que debes ser mayor de edad para registrarte. 
 * He cambiado el formato de la fecha de tipo text a tipo date, esto facilita la introducción de la fecha en dispositivos mobiles y da menos errores a fallos.
 * No he conseguido a traves del modulo eliminar o modificar el mensaje de warning que se arroja cuando la edad es menor a 18 años. Este mensaje es el siguiente: El formato debe ser 31/05/1970.  
*/
class Validate extends ValidateCore
{

    public static function isBirthDate($date, $format = 'Y-m-d')
    {
        if (empty($date) || $date == '0000-00-00')
        {
            return true;
        }

        $d = DateTime::createFromFormat($format, $date);

        if (!empty(DateTime::getLastErrors()['warning_count']) || false === $d && Validate::isBirthDate($date) == false) 
        {
            echo "<script>alert('Debes ser mayor que 18 años para registarte.')</script>";
            return false;
        }

        // Intervalo de fecha de nacimiento 
        $d->add(new DateInterval("P18Y"));
        return $d->setTime(0, 0, 0)->getTimestamp() <= time();
    }    
}