tps = 
[
	{ 
		name       : "accommodation",
		attributes : [
			"owner",
			"address",
			"amenities",
			"title",
			"description",
			"images",
			"thumbnail",
			"url",
			"rateType",
			"maxGuests",
			"rate",
			"special_rate",
			"booked"
		] 
	},
	{ 
	 	name       : "hotel", 
	  	parent     : "accommodation",
		attributes :  ["starRating"] 
	},
	{ 
		name       : "farm_house", 
		parent     : "accommodation",
		attributes : ["size","activities"] 
	},
	{ 	name       : "apartment", 
		parent     : "accommodation", 
		attributes : ["aptType"]
	},	
	{ 	name       : "resort", 
		parent     : "accommodation",
		attributes : ["size","activities","specalities"] 
	},
	{ 	
		name       : "address",
		attributes : 
		["streetNumber","street","line1","line2", "line3",
		 "city", "state", "country","pincode",
		 "neighbourhood","lat","long"] 
	},
	{ 	
		name 	   : "owner", 
		attributes : ["userId"],
		embedded   : "false"
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

//default is string, single-valued, keyword, faceted
//only non-default need to be specified
attrs = [
	{
	    name     : "price" ,
	    datatype : "numeric", 
	    valuetype: "range" , 
	    keyword  : "false" , 
	    facet    : "false"
	},
	{
	    name     : "amenities",
	    //valuetype: "enumerated",
	    valuetype: "multi-valued",	
	    keyword  : "true",
	    facet    : "true"
	},
	{
	    name     : "activities",
	    valuetype: "multi-valued",	
	    keyword  : "true",
	    facet    : "true"
	},
	{
	    name     : "images",
	    valuetype: "multi-valued",	
	    keyword  : "true",
	    facet    : "true"
	},
	{
	    name     : "aptType",
	    valuetype: "multi-valued",	
	    keyword  : "true",
	    facet    : "true"
	},
	{
	    name     : "lat",
	    datatype : "numeric", 
	    keyword  : "false",
            facet    : "false" 
	},
	{
	    name     : "long",
	    datatype : "numeric", 
	    keyword  : "false",
            facet    : "false" 
	}, 
	{
	    name     : "rateType",
	    datatype : "string", 
	    keyword  : "false",
        facet    : "false" 
	}, 
	{
	    name     : "streetNumber",
	    keyword  : "false",
        facet    : "false" 
	},
	{
	    name     : "userId",
	    keyword  : "false",
            facet    : "false" 
	},
	{
	    name     : "maxGuests",
	    datatype : "numeric",
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
	    name     : "daily",
	    datatype : "float",
	    keyword  : "false",
            facet    : "false" 
	},
	{
	    name     : "weekly",
	    datatype : "float",
	    keyword  : "false",
            facet    : "false" 
	},
	{
	    name     : "monthly",
	    datatype : "float",
	    keyword  : "false",
            facet    : "false" 
	},
];

enums = [
    { "amenities" : ["Television", "Telephone", "Wifi", "Laundry", "Health Club", "Sauna"] }
]

print("creating the database test.. standing for vacation rental..");
conn = new Mongo();
db = conn.getDB("test");
//db.dropDatabase();
print("Inserting meta information about attributes...");
db.attributes.remove();
for each (attr in attrs) {
	db.attributes.save(attr);	
}
print("Inserted attribute meta data...");
db.types.remove();
print("Inserting vacation listing type definitions...");
for each (t in tps) {
	db.types.save(t);
}
print("inserted vacation listing type definitions...");
db.enumerations.remove();
print("Inserting attribute enumeration type definitions...");
for each (t in enums) {
	db.enumerations.save(t);
}
print("inserted attribute enumeration type definitions...");

//db.types.find();
db.listings.remove();
print("Done!!!");
