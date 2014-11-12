SELECT 
	v.VendorGuid AS code, v.VendorName AS name, 
	c.CtgName AS category, 
	a.Address, 
	[dbo].Fn_GetVendorServiceTime(v.VendorGuid) AS service_time,
	'http://58.214.25.78:3128/images/wuxi/vendor/'+CAST(v.VendorGuid AS nvarchar(40))+'.jpg' AS logo,
	'http://58.214.25.78:3128/images/wuxi/vendor/'+CAST(v.VendorGuid AS nvarchar(40))+'.jpg' AS logo_small,
	v.Description AS intro,
	a.Longitude AS longitude,
	a.Latitude AS latitude,
	e.AverageCost AS average_cost,
	'5000' AS express_range,
	v.Disabled AS disabled
FROM Vendor AS v

LEFT JOIN VendorAddress AS a
	ON a.VendorGuid=v.VendorGuid
LEFT JOIN Region AS r
	ON r.RegionGuid=v.RegionGuid
LEFT JOIN CategoryGroup AS ctg
	ON ctg.CtgGroupGuid=v.CtgGroupGuid
LEFT JOIN CategoryGroupMember AS cgm
	ON cgm.CtgGroupGuid=v.CtgGroupGuid
LEFT JOIN Category AS c
	ON c.CtgGuid=cgm.CtgGuid
LEFT JOIN CategoryStandard AS cs
	ON cs.CtgStdGuid=c.CtgStdGuid
LEFT JOIN W_VendorExtend AS e
	ON e.VendorGuid=v.VendorGuid

WHERE v.Disabled='0' AND cs.CtgStdName=N'商家分类' AND v.VendorName!=N'迷你超市'