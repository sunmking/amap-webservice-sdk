<?php

namespace Sunmking\Amap;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Sunmking\Amap\exception\InvalidConfigException;
use Sunmking\Amap\exception\InvalidException;

class WebService
{
    const API_V3_URL = 'http://restapi.amap.com/v3';
    const API_V4_URL = 'http://restapi.amap.com/v4';
    const API_V5_URL = 'http://restapi.amap.com/v5';
    /**
     * 事件查询 URL
     */
    const ET_API_URL = 'https://et-api.amap.com/event/';
    /**
     * 地理编码
     */
    const GEO_URL = '/geocode/geo';
    /**
     * 逆地理编码
     */
    const REGEO_URL = '/geocode/regeo';
    /**
     * 路线规划  步行
     */
    const WALKING_URL = '/direction/walking';
    /**
     * 路线规划  公交路径规划
     */
    const TRANSIT_INTEGRATED_URL = '/direction/transit/integrated';
    /**
     * 路线规划 驾骑行路径规划
     */
    const DRIVE_URL = '/direction/driving';
    /**
     * 路线规划 骑行路径规划
     */
    const BICYCLING_URL = '/direction/bicycling';
    /**
     * 路线规划 电动车路径规划
     */
    const ELECTROBIKE_URL = '/direction/electrobike';
    /**
     * 路线规划 未来路径规划
     */
    const ETD_DRIVE_URL = '/etd/driving';
    /**
     * 路线规划 距离测量
     */
    const DISTANCE_URL = '/distance';
    /**
     * 行政区域查询
     */
    const DISTRICT_URL = '/config/district';
    /**
     * 搜索POI  关键字搜索
     */
    const TEXT_SEARCH_URL = '/place/text';
    /**
     * 搜索POI  周边搜索
     */
    const AROUND_SEARCH_URL = '/place/around';
    /**
     * 搜索POI  多边形搜索
     */
    const POLYGON_SEARCH_URL = '/place/polygon';
    /**
     * 搜索POI  ID查询
     */
    const DETAIL_SEARCH_URL = '/place/detail';
    /**
     * 交通事件
     */
    const QUERY_BY_ADCODE = '/queryByAdcode';
    /**
     * ip 定位
     */
    const IP_URL = '/ip';
    /**
     * 静态地图
     */
    const STATIC_MAP_URL = '/staticmap';
    /**
     * 坐标转换
     */
    const CONVERT_URL = '/assistant/coordinate/convert';
    /**
     * 天气查询
     */
    const WEATHER_URL = '/weather/weatherInfo?';

    /**
     * 输入提示
     */
    const INPUT_TIPS_URL = '/assistant/inputtips';
    /**
     * 交通态势 矩形
     */
    const RECTANGLE_TRAFFIC_URL = '/traffic/status/rectangle';
    /**
     * 交通态势 圆形
     */
    const CIRCLE_TRAFFIC_URL = '/traffic/status/circle';
    /**
     * 交通态势 指定线路交通态势
     */
    const ROAD_TRAFFIC_URL = '/traffic/status/road';
    /**
     * 轨迹纠偏
     */
    const GRASPROAD_DRIVING = '/grasproad/driving';
    /**
     * @var string
     */
    public $key;

    /**
     * @var false|mixed
     */
    private $sign;

    private $private_key;
    /**
     * @var array
     */
    public $guzzleOptions = [];

    /**
     * @throws InvalidConfigException
     */
    public function __construct($options)
    {
        $this->sign = $options['sign'] ?? false;
        $this->private_key = $options['private_key'] ?? '';
        $this->key = $options['key'] ?? '';
        if (empty($this->key)) {
            throw new InvalidConfigException('The "key" property must be set.');
        }
        if (false!==$this->sign && empty($this->private_key)) {
            throw new InvalidConfigException('The "private_key" property must be set.');
        }
    }

    /**
     * @return Client
     */
    public function getHttpClient(): Client
    {
        return new Client($this->guzzleOptions);
    }

    /**
     * @param array $options
     */
    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    /**
     * 地址编码
     * @throws InvalidException
     * @throws GuzzleException
     */
    public function getGeo($address, $city='',$format = 'json')
    {
        if (empty($address)) {
            throw new InvalidException('address property must be set');
        }

        $query = array_filter([
            'key' => $this->key,
            'address' => $address,
            'city' => $city,
        ]);

        return $this->getRequest($query,self::API_V3_URL.self::GEO_URL,$format);
    }

    /**
     * 逆地址编码
     * @throws InvalidException
     * @throws GuzzleException
     */
    public function getRegeo($params,$type="base",$format = 'json')
    {
        if (!isset($params['location'])) {
            throw new InvalidException('location property must be set');
        }

        if (!\in_array(\strtolower($type), ['base', 'all'])) {
            throw new InvalidException('Invalid type value(base/all): '.$type);
        }

        $query = array_filter([
            'key' => $this->key,
            'location' => $params['location'],
            'poitype' => $params['poitype']??'',
            'radius' => $params['radius']??'1000',
            'roadlevel' => $params['roadlevel']??'',
            'callback' => $params['callback']??'',
            'homeorcorp' => $params['homeorcorp']??0,
            'extensions' => $type,
        ]);

        return $this->getRequest($query,self::API_V3_URL.self::REGEO_URL,$format);
    }

    /**
     * 步行路径规划
     * @throws InvalidException|GuzzleException
     */
    public function walking($origin,$destination,$format = 'json')
    {
        if (empty($origin)) {
            throw new InvalidException('origin property must be set');
        }
        if (empty($destination)) {
            throw new InvalidException('destination property must be set');
        }

        $query = array_filter([
            'key' => $this->key,
            'origin' => $origin,
            'destination' => $destination,
            'callback' => '',
        ]);

        return $this->getRequest($query,self::API_V3_URL.self::WALKING_URL,$format);
    }

    /**
     * @param $city
     * @param string $format
     * @return mixed|string
     * @throws InvalidException
     * @throws GuzzleException
     */
    public function getLiveWeather($city, string $format = 'json')
    {
        return $this->getWeather($city, 'base', $format);
    }

    /**
     * @param $city
     * @param string $format
     * @return mixed|string
     * @throws InvalidException
     * @throws GuzzleException
     */
    public function getForecastsWeather($city, string $format = 'json')
    {
        return $this->getWeather($city, 'all', $format);
    }

    /**
     * @param $city
     * @param string $type
     * @param string $format
     * @return mixed|string
     * @throws InvalidException
     * @throws GuzzleException
     */
    public function getWeather($city, string $type = 'base', string $format = '')
    {
        if (!\in_array(\strtolower($type), ['base', 'all'])) {
            throw new InvalidException('Invalid type value(base/all): '.$type);
        }


        $query = array_filter([
            'key' => $this->key,
            'city' => $city,
            'extensions' => $type,
            'callback' => '',
        ]);
        return $this->getRequest($query,self::API_V3_URL.self::WEATHER_URL,$format);
    }

    /**
     * 数字签名算法
     *
     * @param array $data
     * @return string
     */
    private function signature(array $data = []): string
    {
        ksort($data, SORT_STRING);
        $tmpStr = '';
        foreach ($data as $key => $value) {
            if (strlen($tmpStr) == 0) {
                $tmpStr .= $key . "=" . $value;
            } else {
                $tmpStr .= "&" . $key . "=" . $value;
            }
        }
        $tmpStr .= $this->private_key;
        return md5($tmpStr);
    }

    /**
     * @param array $query
     * @param string $url
     * @param string $format
     * @return mixed
     * @throws InvalidException|GuzzleException
     */
    public function getRequest(array $query, string $url='', string $format='json')
    {
        if (empty($url)) {
            throw new InvalidException('url property must be set');
        }

        if (!\in_array(\strtolower($format), ['xml', 'json'])) {
            throw new InvalidException('Invalid response format: '.$this->dataType);
        }

        $query['output'] = $format;

        if ($this->sign) {
            $query['sig'] = $this->signature($query);
        }

        try {
            $response = $this->getHttpClient()->get($url, [
                'query' => $query,
            ])->getBody()->getContents();

            return 'json' === $format ? \json_decode($response, true) : $response;
        } catch (\Exception $e) {
            throw new RequestException($e->getMessage(), $e->getCode());
        }
    }
}