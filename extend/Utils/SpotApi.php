<?php

namespace Utils;

class SpotApi extends Utils
{


    const  SPOT_EXCHANGE_RATE='/api/v5/market/exchange-rate';

    // 获取法币汇率
  static  public  function getExchangeRate()
    {
        return self::request(self::SPOT_EXCHANGE_RATE, [], 'GET');
    }

}