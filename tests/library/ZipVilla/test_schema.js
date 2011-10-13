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
			"rate",
			"rateType",
			"maxGuests"
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
	}
];

//default is string, single-valued, keyword, faceted
//only non-default need to be specified
attrs = [
	{
	    name     : "price" ,
	    dataType : "numeric", 
        valueType: "range" , 
	    keyword  : "false" , 
	    facet    : "false"
	},
	{
	    name     : "amenities",
	    valueType: "multi-valued",	
	    keyword  : "true",
	    facet    : "true"
	},
	{
	    name     : "activities",
	    valueType: "multi-valued",	
	    keyword  : "true",
	    facet    : "true"
	},
	{
	    name     : "images",
	    valueType: "multi-valued",	
	    keyword  : "true",
	    facet    : "true"
	},
	{
	    name     : "aptType",
	    valueType: "multi-valued",	
	    keyword  : "true",
	    facet    : "true"
	},
	{
	    name     : "lat",
	    dataType : "numeric", 
	    keyword  : "false",
        facet    : "false" 
	},
	{
	    name     : "long",
	    dataType : "numeric", 
	    keyword  : "false",
        facet    : "false" 
	}, 
	{
	    name     : "rate",
	    dataType : "numeric", 
	    keyword  : "false",
        facet    : "false" 
	},
	{
	    name     : "rateType",
	    dataType : "string", 
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
	    dataType : "numeric",
	    keyword  : "false",
        facet    : "false" 
	}
];

print("creating the database vr.. standing for vacation rental..");
conn = new Mongo();
db = conn.getDB("vr");
//db.dropDatabase();
//db = conn.getDB("vr");
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
//db.types.find();
//db.listings.remove();
print("Done!!!");
