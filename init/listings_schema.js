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
                        "entertainment_options",
                        "kitchen_amenities",
                        "bedroom_amenities",
                        "outdoor_activities",
                        "location_and_view",
                        "communications_equipment",
                        "suitability",
                        "nearby",
                        // price details
                        "daily_rate",
                        "weekly_rate",
                        "monthly_rate",
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
	    valuetype: "multi-valued",	
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
	}
];

print("[1] creating database 'vr'.");
conn = new Mongo();
db = conn.getDB("vr");

print("[2] emptying db of existing type, attribute and listing data.");
db.listings.remove();
db.types.remove();
db.attributes.remove();

print("[3] adding attributes.");
for each (attr in attrs) {
	db.attributes.save(attr);	
}

print("[4] adding type definitions.");
for each (t in tps) {
	db.types.save(t);
}

print("[5] database initialized.");
