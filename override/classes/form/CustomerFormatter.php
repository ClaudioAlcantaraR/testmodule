<?php
/**
 * Con este override cambiamos el formato del campo fecha de nacimiento.
 * Anteriormente estaba en tipo text y ahora tipo date
 */
class CustomerFormatter extends CustomerFormatterCore
{
    public function getFormat()
    {
        $changingBirthDate = parent::getFormat();
        $changingBirthDate['birthday']->setType('date');
        return $changingBirthDate;
    }
}