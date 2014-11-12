SELECT --p.VendorGuid,
	店名 = MAX(p.VendorName)
	--, i.ItemGuid
	, 菜名 = MAX(i.ItemName)
	, 数量 = SUM(oi.ItemQty)
	, 金额 = SUM(oi.TotalAmount)
	, 销售额 = SUM(oiv.SumAmount)
FROM dbo.OrderLastVersion lv
INNER JOIN dbo.OrderVersion v
ON v.OrdVerGuid = lv.OrdVerGuid
INNER JOIN dbo.SalesVersion sv
ON sv.SalesVerGuid = v.SalesVerGuid
INNER JOIN dbo.Purchase p
ON p.OrderGuid = v.OrderGuid
INNER JOIN dbo.OIV_Item iv
ON iv.OIVGuid = v.OIVGuid
INNER JOIN dbo.OrderItemVersion oiv
ON oiv.OIVGuid = iv.OIVGuid
INNER JOIN dbo.OrderItem oi
ON oi.OrdItemGuid = iv.OrdItemGuid
INNER JOIN dbo.Item i
ON i.ItemGuid = oi.ItemGuid
INNER JOIN dbo.CategoryGroupMember cgm
ON cgm.CtgGroupGuid = i.CtgGroupGuid
INNER JOIN dbo.Category c
ON c.CtgGuid = cgm.CtgGuid
WHERE c.CtgName = N'特价套餐'
	--AND sv.ReqDate >= '2013-1-3'
	--AND sv.ReqDate < '2013-1-10'
	--AND sv.ReqDate >= '2012-12-28'
	--AND sv.ReqDate < '2013-1-3'
	AND sv.ReqDate >= '2012-12-21'
	AND sv.ReqDate < '2012-12-28'
GROUP BY p.VendorGuid, i.ItemGuid
ORDER BY 店名, 菜名