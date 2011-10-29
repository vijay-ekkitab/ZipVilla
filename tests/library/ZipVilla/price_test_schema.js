homes = [
	{
		title : "Grand View of the Sea",
		type : "home",
		city : "Goa",
        rate : {"daily":100, "weekly":600, "monthly":2000},
		special_rate : [
            {period: {from: new Date(2011,11,01), to: new Date(2011,11,10)}, 
             rate: {"daily":120, "weekly":700, "monthly":2500}},
            {period: {from: new Date(2011,11,15), to: new Date(2012,0,05)}, 
             rate: {"daily":150, "weekly":900, "monthly":3000}}
		]
	}
]
conn = new Mongo();
db = conn.getDB("test");
db.homes.remove();
for each (home in homes) {
	db.homes.save(home);
}
print("initialization completed.");
