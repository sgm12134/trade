<?php


class SpotApi extends Utils
{


    const  SPOT_EXCHANGE_RATE='/api/v5/market/exchange-rate';

    // 获取法币汇率
    public function getExchangeRate()
    {
        return $this->request(self::SPOT_EXCHANGE_RATE, [], 'GET');
    }

}