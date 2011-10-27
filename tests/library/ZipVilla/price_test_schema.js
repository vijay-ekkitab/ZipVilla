homes = [
	{
		title : "Grand View of the Sea",
		type : "home",
		city : "Goa",
        standard_rate : "100, 600, 2000",
		special_rate : [
            {from: new Date(2011,11,01), to: new Date(2011,11,10), rate: "120, 700, 2500"},
			{from: new Date(2011,11,15), to: new Date(2012,0,05), rate : "150, 900, 3000"}
		]
	},
]
conn = new Mongo();
db = conn.getDB("test");
db.homes.remove();
for each (home in homes) {
	db.homes.save(home);
}
print("initialization completed.");
