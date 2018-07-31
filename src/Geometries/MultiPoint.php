<?php namespace Phaza\LaravelPostgis\Geometries;

class MultiPoint extends PointCollection implements GeometryInterface, \JsonSerializable
{
    public function is3d()
    {
        if(count($this->points) === 0) return false;
        return $this->points[0]->is3d();
    }
    
    public function toWKT()
    {
        $wktType = 'MULTIPOINT';
        if($this->is3d()) $wktType .= ' Z';
        return sprintf('%s(%s)', $wktType, (string)$this);
    }

    public static function fromWKT($wkt)
    {
        $wktArgument = Geometry::getWKTArgument($wkt);

        return static::fromString($wktArgument);
    }

    public static function fromString($wktArgument)
    {
        $matches = [];
        preg_match_all('/\(\s*(\d+\s+\d+)\s*\)/', trim($wktArgument), $matches);

        if (count($matches) < 2) {
            return new static([]);
        }

        $points = array_map(function ($pair) {
            return Point::fromPair($pair);
        }, $matches[1]);

        return new static($points);
    }

    public function __toString()
    {
        return implode(',', array_map(function (Point $point) {
            return sprintf('(%s)', $point->toPair());
        }, $this->points));
    }

    /**
     * Convert to GeoJson MultiPoint that is jsonable to GeoJSON
     *
     * @return \GeoJson\Geometry\MultiPoint
     */
    public function jsonSerialize()
    {
        $points = [];
        foreach ($this->points as $point) {
            $points[] = $point->jsonSerialize();
        }

        return new \GeoJson\Geometry\MultiPoint($points);
    }
}
