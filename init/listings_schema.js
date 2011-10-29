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
                        "entertainment_options",
                        "kitchen_amenities",
                        "bedroom_amenities",
                        "outdoor_activities",
                        "location_and_view",
                        "communications_equipment",
                        "suitability",
                        "nearby",
                        // price details
                        "rate",
                        "special_rate",
                        // availability
                        "booked",
                        // collateral
			            "title",
			            "description",
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
                      "full_address",
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
                      "longtitude"
                     ]
    },

	{ 	
		name 	   : "owner", 
		attributes : ["owner_id"]
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
	    name     : "daily_rate",
	    datatype : "float", 
	    keyword  : "false", 
	    facet    : "true"
	},
	{
	    name     : "weekly_rate",
	    datatype : "float", 
	    keyword  : "false", 
	    facet    : "true"
	},
	{
	    name     : "monthly_rate",
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
	    name     : "entertainment_options",
	    valuetype: "enumerated",	
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
	    name     : "kitchen_amenities",
	    valuetype: "multi-valued",	
	    keyword  : "false",
	    facet    : "true"
	},
	{
	    name     : "bedroom_amenities",
	    valuetype: "multi-valued",	
	    keyword  : "false",
	    facet    : "true"
	},
	{
	    name     : "outdoor_activities",
	    valuetype: "multi-valued",	
	    keyword  : "false",
	    facet    : "true"
	},
	{
	    name     : "location_and_view",
	    valuetype: "multi-valued",	
	    keyword  : "false",
	    facet    : "false"
	},
	{
	    name     : "communications_equipment",
	    valuetype: "multi-valued",	
	    keyword  : "false",
	    facet    : "true"
	},
	{
	    name     : "suitability",
	    valuetype: "multi-valued",	
	    keyword  : "false",
	    facet    : "true"
	},
	{
	    name     : "nearby",
	    valuetype: "multi-valued",	
	    keyword  : "false",
	    facet    : "false"
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
        facet    : "false" 
	},
	{
	    name     : "longtitude",
	    datatype : "float", 
	    keyword  : "false",
        facet    : "false" 
	}, 
	{
	    name     : "street_number",
	    keyword  : "false",
        facet    : "false" 
	}, 
	{
	    name     : "city",
	    keyword  : "true",
        facet    : "true" 
	}, 
	{
	    name     : "state",
	    keyword  : "true",
        facet    : "true" 
	}, 
	{
	    name     : "zipcode",
	    keyword  : "true",
        facet    : "true" 
	}, 
	{
	    name     : "longtitude",
	    datatype : "float", 
	    keyword  : "false",
        facet    : "false" 
	}, 
	{
	    name     : "owner_id",
	    datatype : "integer", 
	    keyword  : "false",
        facet    : "false" 
	},
	{
	    name     : "maxGuests",
	    datatype : "integer",
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
	}
];

enums = [
         { "entertainment_options" : ["Television", "Radio"],
           "onsite_services" : ["Laundry", "Cook", "Cleaning", "Concierge"] }
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
