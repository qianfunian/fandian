SELECT v.* , [dbo].Fn_IsInVendorServiceTime(v.VendorGuid, GETDATE(), 0, 0) AS InService, 
[dbo].Fn_IsInVendorServiceTime(v.VendorGuid, GETDATE(), -30, -30) AS OpenWebService, 
[dbo].Fn_GetVendorServiceTime(v.VendorGuid) AS ServiceTimeString,
ctg.CtgGroupName,
c.CtgName,
cs.CtgStdName,
a.Address,
e.HotRate,e.HasLogo, e.IsIdxRec, e.IsRec
,[dbo].Fn_GetDistance(a.CoordValue, 120.31630109003, 31.563797923327) AS Distance
FROM Vendor AS v

LEFT JOIN VendorAddress AS a
	ON a.VendorGuid=v.VendorGuid
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

WHERE v.Disabled='0' AND cs.CtgStdName=N'商家分类' AND v.VendorName!=N'迷你超市' AND v.VendorGuid IN (
	SELECT DISTINCT i.VendorGuid
	FROM Item AS i
		INNER JOIN Vendor AS v
			ON v.VendorGuid=i.VendorGuid
		INNER JOIN ServiceCombin AS sc
			ON sc.SrvCmbGuid=i.SrvCmbGuid
		INNER JOIN ServiceCombinMember AS scm
			ON scm.SrvCmbGuid=sc.SrvCmbGuid
		INNER JOIN Service AS s
			ON s.ServiceGuid=scm.ServiceGuid
	WHERE v.Disabled=0 AND i.Disabled=0 AND s.ServiceName=N'普通'
)