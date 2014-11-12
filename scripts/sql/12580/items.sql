SELECT 
	i.VendorGuid AS vendor_code,
	c.CtgName AS category,
	i.ItemGuid AS code,
	i.ItemName AS name,
	i.UnitPrice AS price,
	u.UnitName AS unit,
	i.Description AS description,
	CASE 
		WHEN ie.HasLogo=1
	THEN
		'http://58.214.25.78:3128/images/wuxi/items/'+CAST(i.VendorGuid AS nvarchar(40))+'/'+CAST(i.ItemGuid AS nvarchar(40))+'.jpg'
	ELSE
		'http://58.214.25.78:3128/design/images/nopic.jpg'
	END AS logo

	FROM Item AS i
		INNER JOIN ItemUnit AS u
			ON u.UnitGuid=i.UnitGuid
		INNER JOIN Vendor AS v
			ON v.VendorGuid=i.VendorGuid
		INNER JOIN CategoryGroup AS ctg
			ON ctg.CtgGroupGuid=i.CtgGroupGuid
		INNER JOIN CategoryGroupMember AS cgm
			ON cgm.CtgGroupGuid=i.CtgGroupGuid
		INNER JOIN Category AS c
			ON c.CtgGuid=cgm.CtgGuid
		INNER JOIN CategoryStandard AS cs
			ON cs.CtgStdGuid=c.CtgStdGuid
		LEFT JOIN W_ItemExtend AS ie
			ON ie.ItemGuid=i.ItemGuid

WHERE cs.CtgStdName=N'菜品分类' 