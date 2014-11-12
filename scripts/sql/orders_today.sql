SELECT TOP 1000  o.OrderGuid, o.OrderId, 
	sv.SalesAttribute,
	e.ElementName,
	es.ElementName,
	ov.StatusId, ov.TotalAmount,
	fv.Freight, fv.Distance, fv.Remark,
	v.VendorName, 
	s.IsNewCust, s.CallPhone, 
	sv.CustName, sv.CustAddress, sv.CoordName, CASE WHEN fv.ReqTimeStart IS NULL THEN NULL ELSE CONVERT(VARCHAR(19), fv.ReqDateTime, 120) END AS RequestTime,
	o.AddTime
FROM [Order] AS o
	INNER JOIN OrderVersion AS ov
		ON ov.OrderGuid=o.OrderGuid
	INNER JOIN OrderLastVersion AS olv
		ON olv.OrdVerGuid=ov.OrdVerGuid
	INNER JOIN Purchase AS p
		ON p.PurchGuid=o.PurchGuid
	INNER JOIN Vendor AS v
		ON v.VendorGuid=p.VendorGuid
	INNER JOIN SalesVersion AS sv
		ON sv.SalesVerGuid=ov.SalesVerGuid
	INNER JOIN Sales AS s
		ON s.SalesGuid=sv.SalesGuid
	INNER JOIN FreightVersion AS fv
		ON fv.FrtVerGuid=ov.FrtVerGuid
	INNER JOIN Enum AS e
		ON e.[Language]='zh-CN' AND e.EnumName=N'付款方式' AND e.ElementValue=fv.PaymentMethod
	INNER JOIN Enum AS es
		ON es.[Language]='zh-CN' AND es.EnumName=N'销售来源' AND es.ElementValue=s.SalesSource
WHERE o.AddTime BETWEEN CAST(CONVERT(VARCHAR(10), GETDATE(), 120) AS DATETIME) AND CAST(CONVERT(VARCHAR(10), DATEADD(day, 1, GETDATE()), 120) AS DATETIME)
ORDER BY o.AddTime DESC