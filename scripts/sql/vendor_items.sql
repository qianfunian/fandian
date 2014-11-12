SELECT TOP 9999 i.ItemGuid,i.ItemId,i.ItemName,i.UnitPrice,i.MinOrderQty,i.Disabled,i.VendorGuid, i.ItemQty, i.BoxQty, i.BoxUnitPrice, i.Description,
u.UnitName, v.VendorName, ctg.CtgGroupName, c.CtgName, c.CtgGuid, ie.HasLogo, ie.IsRec, ie.Sales
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

WHERE v.VendorName='喜洋洋' AND cs.CtgStdName='菜品分类' AND i.Disabled=0 AND v.Disabled=0