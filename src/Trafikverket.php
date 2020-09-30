<?php
namespace KFVIT\LaravelTrafikverket;

class Trafikverket {

	public static function trainMessage(){
		return new TrafikverketQueryBuilder("TrainMessage");
	}

	public static function trainStation(){
		return new TrafikverketQueryBuilder("TrainStation");
	}

	public static function trainAnnouncement(){
		return new TrafikverketQueryBuilder("TrainAnnouncement");
	}

	public static function ferryAnnouncement(){
		return new TrafikverketQueryBuilder("FerryAnnouncement");
	}

	public static function ferryRoute(){
		return new TrafikverketQueryBuilder("FerryRoute");
	}

	public static function icon(){
		return new TrafikverketQueryBuilder("Icon");
	}

	public static function roadCondition(){
		return new TrafikverketQueryBuilder("RoadCondition");
	}

	public static function roadConditionOverview(){
		return new TrafikverketQueryBuilder("RoadConditionOverview");
	}

	public static function situation(){
		return new TrafikverketQueryBuilder("Situation");
	}

	public static function weatherStation(){
		return new TrafikverketQueryBuilder("WeatherStation");
	}

}
