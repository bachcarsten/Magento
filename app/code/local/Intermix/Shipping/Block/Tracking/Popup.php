<?php

class Intermix_Shipping_Block_Tracking_Popup extends Mage_Shipping_Block_Tracking_Popup
{
    /**
     * Format given date and time in current locale without changing timezone
     *
     * @param string $date
     * @param string $time
     * @return string
     */
    public function formatDeliveryDateTime($date, $time)
    {
        return $this->formatDeliveryDate($date) . ' ' . $this->formatDeliveryTime($time, $time ? null : $date);
    }
}