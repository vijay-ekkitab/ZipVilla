tps = 
[
	{ 
		name       :    "home",
		attributes : [  
                        // ownership and location 
			            "owner",
			            "address",
                        // physical characteristics
			            "bedrooms",
			            "baths",
                        "guests",
                        "onsite_services",
                        "amenities",
                        "activities",
                        "neighbourhood",
                        "suitability",
                        "house_rules",
                        // price details
                        "rate",
                        "special_rate",
                        // availability
                        "booked",
                        // collateral
			            "title",
			            "description",
			            "rating",
			            "reviews",
			            "images",
			            "video"
		            ] 
	},

    {
        name       : "apartment",
        parent     : "home"
    },

	{ 
		name       : "holiday_home", 
		parent     : "home"
	},

	{ 	
		name       : "address",
		attributes : [
                      "street_number",
                      "street_name", 
                      "location", 
		              "city", 
                      "state", 
                      "country",
                      "zipcode",
                      "coordinates",
		             ] 
	},

    {
        name       : "coordinates",
        attributes : [
                      "latitude",
                      "longitude"
                     ]
    },

    {
		name       : "rate",
		attributes : ["daily", "weekly", "monthly"]
	},
	
	{
		name       : "period",
		attributes : ["from", "to"]
	},
	
	{
		name       : "special_rate",
		attributes : ["period", "rate"],
		repeats    : "true"
	},
	
	{
		name       : "booked",
		attributes : ["period"],
		repeats    : "true"
	}
	
	
];

// an attribute is by default a 'string', 'single-valued', is a keyword that can be searched and is not faceted in search results.
// the following are the exceptions. 

attrs = [
	{
	    name     : "daily",
	    datatype : "integer", 
	    keyword  : "false", 
	    facet    : "true"
	},
	{
	    name     : "weekly",
	    datatype : "float", 
	    keyword  : "false", 
	    facet    : "true"
	},
	{
	    name     : "monthly",
	    datatype : "float", 
	    keyword  : "false", 
	    facet    : "true"
	},
	{
	    name     : "bedrooms",
	    datatype : "integer", 
	    keyword  : "false", 
	    facet    : "true"
	},
	{
	    name     : "baths",
	    datatype : "integer", 
	    keyword  : "false", 
	    facet    : "true"
	},
	{
	    name     : "guests",
	    datatype : "integer", 
	    keyword  : "false", 
	    facet    : "true"
	},
	{
	    name     : "onsite_services",
	    valuetype: "enumerated",	
	    keyword  : "false",
	    facet    : "true"
	},
	{
	    name     : "amenities",
	    valuetype: "enumerated",	
	    keyword  : "false",
	    facet    : "true"
	},
	{
	    name     : "activities",
	    valuetype: "multi-valued",	
	    keyword  : "false",
	    facet    : "false"
	},
	{
	    name     : "neighbourhood",
	    valuetype: "multi-valued",	
	    keyword  : "false",
	    facet    : "false"
	},
	{
	    name     : "suitability",
	    valuetype: "enumerated",
	    keyword  : "false",
	    facet    : "true"
	},
	{
	    name     : "images",
	    valuetype: "multi-valued",	
	    keyword  : "false",
	    facet    : "true"
	},
	{
	    name     : "video",
	    valuetype: "multi-valued",	
	    keyword  : "false",
	    facet    : "true"
	},
	{
	    name     : "latitude",
	    datatype : "float", 
	    keyword  : "false",
        facet    : "true" 
	},
	{
	    name     : "longitude",
	    datatype : "float", 
	    keyword  : "false",
        facet    : "true" 
	}, 
	{
	    name     : "street_number",
	    keyword  : "false",
        facet    : "false" 
	}, 
	{
	    name     : "city",
	    keyword  : "true",
        facet    : "false" 
	}, 
	{
	    name     : "state",
	    keyword  : "true",
        facet    : "false" 
	}, 
	{
	    name     : "zipcode",
	    keyword  : "true",
        facet    : "true" 
	}, 
	{
	    name     : "owner",
	    keyword  : "false",
        facet    : "false" 
	},
	{
	    name     : "from",
	    datatype : "date",
	    keyword  : "false",
        facet    : "false" 
	},
	{
	    name     : "to",
	    datatype : "date",
	    keyword  : "false",
        facet    : "false" 
	},
	{
	    name     : "house_rules",
	    keyword  : "false",
        facet    : "false" 
	},
	{
	    name     : "rating",
	    datatype : "integer",
	    keyword  : "false",
        facet    : "true" 
	},
	{
	    name     : "reviews",
	    datatype : "integer",
	    keyword  : "false",
        facet    : "true" 
	}
];

enums = [
         { 
           "amenities"		: ["Television", "Cable or Satellite TV", "Internet", 
                       		   "Wifi", "Air Conditioning", "Hot Water", "Swimming Pool",
                       		   "Kitchen", "Parking", "Washer Dryer", "Gym"],
                       		   
           "onsite_services": ["Laundry", "Cook", "Cleaning", "Concierge"],
           
           "suitability"    : ["Handicap Access", "Pets Allowed"]
                               
         }
     ]

print("[1] creating database 'vr'.");
conn = new Mongo();
db = conn.getDB("vr");

print("[2] emptying db of existing type, attribute, enumeration and listing data.");
db.listings.remove();
db.types.remove();
db.attributes.remove();
db.enumerations.remove();

print("[3] adding attributes.");
for each (attr in attrs) {
	db.attributes.save(attr);	
}

print("[4] adding type definitions.");
for each (t in tps) {
	db.types.save(t);
}

print("[5] adding attribute enumeration type definitions...");
for each (t in enums) {
	db.enumerations.save(t);
}


print("[6] database initialized.");
