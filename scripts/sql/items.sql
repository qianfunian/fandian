//获取商家曾经点过的菜
SELECT DISTINCT oi.ItemGuid FROM [WuXi_FanDian].[dbo].[OrderItem] as oi
	INNER JOIN [WuXi_FanDian].[dbo].[OrderVersion] as ov
		on ov.OrderGuid=oi.OrderGuid
	inner join [WuXi_FanDian].[dbo].[SalesVersion] AS sv
		on sv.SalesVerGuid=ov.SalesVerGuid
	inner join [WuXi_FanDian].[dbo].[Sales] as s
		on s.SalesGuid=sv.SalesGuid
	inner join [WuXi_FanDian].[dbo].[Purchase] as p
		on p.OrderGuid=ov.OrderGuid
  where s.CustGuid='7C66D384-7033-4A4C-9F03-1B56CB464351' AND p.VendorGuid='77F6C24A-68B9-4CA9-92AC-A062AD246469'