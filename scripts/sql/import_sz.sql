USE [wxfd_allcity];

--需要先在接单系统添加苏州业务、业务组、业务组合等数据，并记下来相关Guid给导入脚本使用

--CategoryStandard唯一性字段导致苏州数据无法导入
--Vendor表的VendorId唯一索引要去掉
--Customer表的数据都归类为苏州城区

--BEGIN TRANSACTION;

--无锡的城市id
DECLARE @WxCityId NVARCHAR(20)='wx';
--苏州的城市id
DECLARE @CityId NVARCHAR(20)='sz';
--苏州的城市Guid
DECLARE @CityGuid NVARCHAR(50)='D647233C-7EF9-4DC0-AC75-6F01366BC1AE';
--是否是套餐
DECLARE @IsSetMeal BIT=0;
--苏州城区配送中心Guid
DECLARE @DCGuidSZ NVARCHAR(50)='9382188F-232D-4961-BDEB-9484F8CBE443';
--苏州城区的AreaGuid
DECLARE @AreaGuidSZ NVARCHAR(50)='A2F85398-695E-42CA-91B3-DD1FAAB6E6C7';
--苏州城区业务组Guid
DECLARE @SrvGrpGuidSZ NVARCHAR(50)='0D9285DD-A789-41A7-88AC-A99AEB70C98B';
--苏州城区业务组名称
DECLARE @SrvGrpNameSZ NVARCHAR(50)='苏州城区业务';
--苏州城区业务Guid
DECLARE @ServiceGuidSZ NVARCHAR(50)='FF3AEA50-756F-4A68-B4AC-C71A50B9BF43';
--苏州城区业务名称
DECLARE @ServiceNameSZ NVARCHAR(50)='普通';
--苏州城区业务组组合Guid
DECLARE @SrvCmbGuidSZ NVARCHAR(50)='24399AA6-1BF6-4C90-B89A-75FF382184ED';
--苏州园区配送中心Guid
DECLARE @DCGuidSZSIP NVARCHAR(50)='20654100-11FD-4C52-88F9-8339BBCD1385';
--苏州园区AreaGuid
DECLARE @AreaGuidSZSIP NVARCHAR(50)='A5688FC6-72BB-43E9-8F9F-5B0C5C70AF53';
--苏州园区业务组Guid
DECLARE @SrvGrpGuidSZSIP NVARCHAR(50)='A44C9CAE-A4CD-4C80-933D-ECFBA83E9D59';
--苏州园区业务组名称
DECLARE @SrvGrpNameSZSIP NVARCHAR(50)='苏州园区业务';
--苏州园区业务Guid
DECLARE @ServiceGuidSZSIP NVARCHAR(50)='96125A19-9EC6-4A07-A692-9972AB462EA2';
--苏州城区业务名称
DECLARE @ServiceNameSZSIP NVARCHAR(50)='普通';
--苏州园区业务组合Guid
DECLARE @SrvCmbGuidSZSIP NVARCHAR(50)='2D933734-EBC5-45CF-8CBC-1F15B09309B3';
--是否已付款
DECLARE @Paid BIT=0;
--文章分类的偏移量（解决自增字段的关联问题）
DECLARE @CategoryOffset INT=1000;
--默认的取菜时间
DECLARE @OrderMinutes INT=0;
--默认的速递类型
DECLARE @StaffType NVARCHAR(50)='全职';
--速递默认密码
DECLARE @Password NVARCHAR(50)='123456';
--默认信息变更(OrderItemVersion）
DECLARE @InfoChanged BIT=1;
--默认地标是否审核
DECLARE @Audited BIT=1;

DECLARE @IssueMethod INT=1;

DECLARE @OldSZRegionGuid NVARCHAR(50)='452EC21F-3194-4A59-B254-599715658799';
DECLARE @OldSZSIPRegionGuid NVARCHAR(50)='F0A16031-CC3A-4C48-A155-2AB7E51FCCC3';
DECLARE @NewSZRegionGuid NVARCHAR(50)='A2F85398-695E-42CA-91B3-DD1FAAB6E6C7';
DECLARE @NewSZSIPRegionGuid NVARCHAR(50)='A5688FC6-72BB-43E9-8F9F-5B0C5C70AF53';

DECLARE @OldSZVIPCtgGroupGuid NVARCHAR(50)='F1BCB1F3-6BE1-47CF-AF1E-649551D770C8';
DECLARE @OldSZNormalCtgGroupGuid NVARCHAR(50)='1B741D9E-0044-4274-8FEA-ECB938698CC0';
DECLARE @NewSZVipCtgGroupGuid NVARCHAR(50)='73852953-D9DA-44F9-8351-267E2301EBBA';
DECLARE @NewSZNormalCtgGroupGuid NVARCHAR(50)='82A76397-F3B6-4F60-9049-625A5330A937';

DECLARE @sSalesGuid NVARCHAR(50), @sCityId NVARCHAR(50), @sVersionId NVARCHAR(50);
DECLARE @sCityGuid NVARCHAR(50), @sAreaGuidSZ NVARCHAR(50), @sSalesSource NVARCHAR(50);
DECLARE @sSrvGrpGuidSZ NVARCHAR(50), @sSrvGrpNameSZ NVARCHAR(50), @sServiceGuidSZ NVARCHAR(50);
DECLARE @sServiceNameSZ NVARCHAR(50), @sCustGuid NVARCHAR(50), @sCustName NVARCHAR(50);
DECLARE @sCategory NVARCHAR(50), @sIsNewCust NVARCHAR(50), @sPhoneGuid NVARCHAR(50);
DECLARE @sAddressGuid NVARCHAR(50), @sCustAddress NVARCHAR(50), @sCoordGuid NVARCHAR(50);
DECLARE @sCoordName NVARCHAR(50), @sReqDate NVARCHAR(50), @sPaid NVARCHAR(50);
DECLARE @sInvoice NVARCHAR(50), @sCommonComment NVARCHAR(50), @sRequestRemark NVARCHAR(50);
DECLARE @sSalesAttribute NVARCHAR(50), @sAddTime NVARCHAR(50), @sCreateTime NVARCHAR(50);
DECLARE @sAddUser NVARCHAR(50), @sCallPhone NVARCHAR(50), @sCoordAddress NVARCHAR(50);

DECLARE @NewWxCityId NVARCHAR(10)='wx.js';
DECLARE @NewSzCityId NVARCHAR(10)='sz.js';

DECLARE @SZFrtGrpGuid NVARCHAR(50)='9153679A-1ADC-4673-AC6B-5BB6DA78CC79';

--Clear first
PRINT 'Clear First ...';
DELETE FROM [dbo].[BlackList] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[Customer] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[CustomerAddress] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[CustomerExpedite] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[CustomerExpedite] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[Deliveryman] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[DistributionCenter] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[DivisionGroup] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[DivisionGroupMember] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[DivisionGroupScheme] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[IssueLastVersion] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[Item] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[Order] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[OrderCancel] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[OrderItem] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[OrderItemVersion] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[OrderLoss] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[OrderStatusLog] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[OrderVersionItem] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[OrderVersion] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[NewWebSales] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[Sales] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[SalesVersion] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[Vendor] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[VendorAddress] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[VendorContactMethod] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[VendorContactPerson] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[VendorCorporate] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[VendorServiceTime] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_Addressbook] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_Article] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_ArticleCategory] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_Attachment] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_Attachment] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_BaiduPlaceLog] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_CoordForBaidu] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_FavoritedItems] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_FavoritedVendors] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_Feedback] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_ItemExtend] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_OrderAnnounce] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_OrderHash] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_ResetPasswordHash] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_SearchLogs] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_SystemVars] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_TencentConnectToken] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_Users] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_VendorExtend] WHERE [CityId]=@CityId;
DELETE FROM [dbo].[W_WeiboOauthToken] WHERE [CityId]=@CityId;

--BEGIN TRY
	--BlackList
	PRINT 'Table [dbo].[BlackList] ...';
	INSERT INTO [dbo].[BlackList]  (
		[BlackListGuid],
		[CityId],
		[CustGuid],
		[AddReason],
		[AddTime],
		[AddUser],
		[CancelReason],
		[CancelTime],
		[CancelUser],
		[Remark]
	) 
	SELECT [BlackListGuid],
			@CityId,
			[CustGuid],
			[AddReason],
			[AddTime],
			[AddUser],
			[CancelReason],
			[CancelTime],
			[CancelUser],
			[Remark]
		FROM [Suzhou_Fandian].[dbo].[BlackList]
		WHERE [BlackListGuid] NOT IN (
			SELECT [BlackListGuid]
				FROM [dbo].[BlackList]
		)
	;
	
	--CallUser	PASS
	
	--CancelReason
	PRINT 'Table [dbo].[CancelReason] ...';
	INSERT INTO [dbo].[CancelReason] (
		[CancelGuid],
		[Initiator],
		[Reason],
		[Disabled],
		[AddUser],
		[AddTime]
	)
	SELECT [CancelGuid],
			[Initiator],
			[Reason],
			[Disabled],
			[AddUser],
			[AddTime]
			FROM [SuZhou_Fandian].[dbo].[CancelReason]
			WHERE [Reason] NOT IN (
				SELECT [Reason]
					FROM [dbo].[CancelReason]
			)
	;
	
	--Category
	PRINT 'Table [dbo].[Category] ...';
	INSERT INTO [dbo].[Category] (
		[CtgGuid],
		[CtgStdGuid],
		[CtgName],
		[Description],
		[Disabled],
		[AddTime],
		[AddUser]		
	)
	SELECT fc.[CtgGuid],
			fcs.[CtgStdGuid],
			fc.[CtgName],
			fc.[Description],
			fc.[Disabled],
			fc.[AddTime],
			fc.[AddUser]
			FROM [SuZhou_Fandian].[dbo].[Category] AS fc
				INNER JOIN [SuZhou_Fandian].[dbo].[CategoryStandard] AS tcs
					ON tcs.CtgStdGuid=fc.CtgStdGuid
				INNER JOIN [dbo].[CategoryStandard] AS fcs
					ON fcs.[CtgStdName]=tcs.[CtgStdName]
			WHERE fc.[CtgName] NOT IN (
				SELECT [CtgName]
					FROM [dbo].[Category]
			)
	;
	
	--CategoryGroup
	PRINT 'Table [dbo].[CategoryGroup] ...';
	INSERT INTO [dbo].[CategoryGroup] (
		[CtgGroupGuid],
		[CtgGroupName],
		[TargetObject],
		[AppliedObject],
		[Description],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [CtgGroupGuid],
			[CtgGroupName],
			[TargetObject],
			[AppliedObject],
			[Description],
			[Disabled],
			[AddTime],
			[AddUser]
			FROM [SuZhou_Fandian].[dbo].[CategoryGroup]
			WHERE [CtgGroupName] NOT IN (
				SELECT [CtgGroupName]
					FROM [dbo].[CategoryGroup]
			)
	;
	DELETE FROM [dbo].[CategoryGroup]
		WHERE [CtgGroupGuid]=@OldSZVIPCtgGroupGuid;
	DELETE FROM [dbo].[CategoryGroup]
		WHERE [CtgGroupGuid]=@OldSZNormalCtgGroupGuid;
	--CategoryGroupMember
	PRINT 'Table [dbo].[CategoryGroupMember] ...';
	INSERT INTO [dbo].[CategoryGroupMember] (
		[CGMGuid],
		[CtgGroupGuid],
		[CtgGuid]
	)
	SELECT cgm.[CGMGuid],
			cgm.[CtgGroupGuid],
			cgm.[CtgGuid]
			FROM [SuZhou_Fandian].[dbo].[CategoryGroupMember] AS cgm
				INNER JOIN [SuZhou_Fandian].[dbo].[Category] AS c
					ON c.[CtgGuid]=cgm.[CtgGuid]
		WHERE c.[CtgName] NOT IN (
			SELECT [CtgName]
				FROM [dbo].[Category]
		)
	;
	INSERT INTO [dbo].[CategoryGroupMember] (
		[CGMGuid],
		[CtgGroupGuid],
		[CtgGuid]
	)
	SELECT NEWID(), cg.[CtgGroupGuid], ocgm.[CtgGuid]
		FROM [dbo].[CategoryGroup] AS cg
			INNER JOIN [SuZhou_Fandian].[dbo].[CategoryGroup] AS ocg
				ON ocg.CtgGroupName=cg.CtgGroupName
			INNER JOIN [SuZhou_Fandian].[dbo].[CategoryGroupMember] AS ocgm
				ON ocgm.[CtgGroupGuid]=ocg.[CtgGroupGuid]
			LEFT JOIN [dbo].[CategoryGroupMember] AS cgm
				ON cgm.[CtgGroupGuid]=cg.[CtgGroupGuid]
			LEFT JOIN [dbo].[Category] AS c
				ON cgm.[CtgGuid]=c.[CtgGuid]
		WHERE cgm.[CtgGuid] IS NULL
	;
	DELETE FROM [dbo].[CategoryGroupMember]
		WHERE [CtgGroupGuid]=@OldSZVIPCtgGroupGuid;
	DELETE FROM [dbo].[CategoryGroupMember]
		WHERE [CtgGroupGuid]=@OldSZNormalCtgGroupGuid;
	--CategoryStandard
	PRINT 'Table [dbo].[CategoryStandard] ...';
	INSERT INTO [dbo].[CategoryStandard] (
		[CtgStdGuid],
		[CtgStdName],
		[TargetObject],
		[IsPrimary],
		[IsRequired],
		[Multichoice],
		[Description],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [CtgStdGuid],
			[CtgStdName],
			[TargetObject],
			[IsPrimary],
			[IsRequired],
			[Multichoice],
			[Description],
			[Disabled],
			[AddTime],
			[AddUser]
			FROM [SuZhou_Fandian].[dbo].[CategoryStandard]
	;
	--Coordinate	PASS
	--CoordinateUpload	PASS
	--Customer
	PRINT 'Table [dbo].[Customer] ...';
	INSERT INTO [dbo].[Customer] (
		[CustGuid],
		[CityId],
		[CityGuid],
		[AreaGuid],
		[CustId],
		[CustName],
		[CtgGroupGuid],
		[Company],
		[Mail],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [CustGuid],
			@CityId,
			@CityGuid,
			@AreaGuidSZ,
			[CustId],
			[CustName],
			[CtgGroupGuid],
			[Company],
			[Mail],
			[Remark],
			[Disabled],
			[AddTime],
			[AddUser]
		FROM [SuZhou_Fandian].[dbo].[Customer]
		WHERE [CustGuid] NOT IN (
			SELECT [CustGuid]
				FROM [dbo].[Customer]
		)
	;
	UPDATE [dbo].[Customer]
		SET [CtgGroupGuid]=@NewSZVIPCtgGroupGuid
		WHERE CtgGroupGuid=@OldSZVIPCtgGroupGuid;
	UPDATE [dbo].[Customer]
		SET [CtgGroupGuid]=@NewSZNormalCtgGroupGuid
		WHERE CtgGroupGuid=@OldSZNormalCtgGroupGuid;
	--CustomerAddress
	PRINT 'Table [dbo].[CustomerAddress] ...';
	INSERT INTO [dbo].[CustomerAddress] (
		[AddressGuid],
		[CityId],
		[CustGuid],
		[CustAddress],
		[CoordGuid],
		[CoordName],
		[CoordValue],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [AddressGuid],
			@CityId,
			[CustGuid],
			[CustAddress],
			[CoordGuid],
			[CoordName],
			[CoordValue],
			[Remark],
			[Disabled],
			[AddTime],
			[AddUser]
		FROM [SuZhou_Fandian].[dbo].[CustomerAddress]
		WHERE [AddressGuid] NOT IN (
			SELECT [AddressGuid]
				FROM [dbo].[CustomerAddress]
		)
	;
	--CustomerExpedite
	PRINT 'Table [dbo].[CustomerExpedite] ...';
	INSERT INTO [dbo].[CustomerExpedite] (
		[XpdGuid],
		[CityId],
		[SalesGuid],
		[ExpediteTime],
		[CustMessage],
		[Remark],
		[AddUser],
		[AddTime]
	)
	SELECT [XpdGuid],
		@CityId,
		[SalesGuid],
		[ExpediteTime],
		[CustMessage],
		[Remark],
		[AddUser],
		[AddTime]
	FROM [SuZhou_Fandian].[dbo].[CustomerExpedite]
	WHERE [XpdGuid] NOT IN (
		SELECT [XpdGuid]
			FROM [dbo].[CustomerExpedite]
	)
	;
	--CustomerPhone
	PRINT 'Table [dbo].[CustomerPhone] ...';
	INSERT INTO [dbo].[CustomerPhone] (
		[PhoneGuid],
		[CityId],
		[CustGuid],
		[PhoneNumber],
		[PhoneType],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [PhoneGuid],
		@CityId,
		[CustGuid],
		[PhoneNumber],
		[PhoneType],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	FROM [SuZhou_Fandian].[dbo].[CustomerPhone]
	WHERE [PhoneGuid] NOT IN (
		SELECT [PhoneGuid]
			FROM [dbo].[CustomerPhone]
	)
	;
	--CustOrder
	PRINT 'Table [dbo].[CustOrder] ...';
	INSERT INTO [dbo].[CustOrder] (
		[Phone],
		[OrderTime],
		[CustGuid]
	)
	SELECT [Phone],
			[OrderTime],
			[CustGuid]
			FROM [SuZhou_Fandian].[dbo].[CustOrder]
	;
	--D_Chat	PASS
	
	--D_Checkout	PASS
	
	--D_DeliveryOrder	PASS
	
	--D_GpsOffset	PASS
	
	--D_HistoryGps	PASS
	
	--D_UploadPoint	PASS
	
	--D_VendorDelay	PASS
	
	--DailyDlvSchedule	PASS
	
	--Deliveryman
	PRINT 'Table [dbo].[Deliveryman] ...';
	INSERT INTO [dbo].[Deliveryman] (
		[DlvManGuid],
		[CityId],
		[DCGuid],
		[DlvManId],
		[DlvManName],
		[StaffType],
		[WorkPhone],
		[Password],
		[InputCode],
		[Disabled],
		[AddTime],
		[AddUser],
		[LastHeartBeat],
		[LastLongitude],
		[LastLatitude]
	)
	SELECT [DlvManGuid],
		@CityId,
		[DCGuid],
		[DlvManId],
		[DlvManName],
		@StaffType,
		[WorkPhone],
		@Password,
		[InputCode],
		[Disabled],
		[AddTime],
		[AddUser],
		[LastHeartBeat],
		[LastLongitude],
		[LastLatitude]
		FROM [SuZhou_Fandian].[dbo].[Deliveryman]
	;
	
	--DeliverySchedule	PASS
	
	--DeliveryScheduleTime	PASS
	
	--DeliveryShift	PASS
	
	--DeliveryShiftTime	PASS
	
	--DistributionCenter
	PRINT 'Table [dbo].[DistributionCenter] ...';
	INSERT INTO [dbo].[DistributionCenter] (
		[DCGuid],
		[CityId],
		[CityGuid],
		[AreaGuid],
		[DCName],
		[Phone],
		[Longitude],
		[Latitude],
		[Coordinate],
		[Address],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [DCGuid],
		@CityId,
		@CityGuid,
		@AreaGuidSZ,
		[DCName],
		[Phone],
		[Longitude],
		[Latitude],
		[Coordinate],
		[Address],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [SuZhou_Fandian].[dbo].[DistributionCenter]
	WHERE [DCGuid]=@DCGuidSZ;
	--DistributionCenter SZ SIP
	PRINT 'Table [dbo].[DistributionCenter] ...';
	INSERT INTO [dbo].[DistributionCenter] (
		[DCGuid],
		[CityId],
		[CityGuid],
		[AreaGuid],
		[DCName],
		[Phone],
		[Longitude],
		[Latitude],
		[Coordinate],
		[Address],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [DCGuid],
		@CityId,
		@CityGuid,
		@AreaGuidSZSIP,
		[DCName],
		[Phone],
		[Longitude],
		[Latitude],
		[Coordinate],
		[Address],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [SuZhou_Fandian].[dbo].[DistributionCenter]
	WHERE [DCGuid]=@DCGuidSZSIP;
	;
	--DivisionGroup
	PRINT 'Table [dbo].[DivisionGroup] ...';
	INSERT INTO [dbo].[DivisionGroup] (
		[DivGrpGuid],
		[CityId],
		[DGSGuid],
		[DivGrpName],
		[Description],
		[AddTime],
		[AddUser]
	)
	SELECT [DivGrpGuid],
		@CityId,
		[DGSGuid],
		[DivGrpName],
		[Description],
		[AddTime],
		[AddUser]
		FROM [SuZhou_Fandian].[dbo].[DivisionGroup]
	;
	--DivisionGroupMember
	PRINT 'Table [dbo].[DivisionGroupMember] ...';
	INSERT INTO [dbo].[DivisionGroupMember] (
		[DGMGuid],
		[CityId],
		[DivGrpGuid],
		[MemberGuid],
		[MemberName],
		[AddTime],
		[AddUser]
	)
	SELECT [DGMGuid],
			@CityId,
			[DivGrpGuid],
			[MemberGuid],
			[MemberName],
			[AddTime],
			[AddUser]
			FROM [SuZhou_Fandian].[dbo].[DivisionGroupMember]
	;
	--DivisionGroupScheme
	PRINT 'Table [dbo].[DivisionGroupScheme] ...';
	INSERT INTO [dbo].[DivisionGroupScheme] (
		[DGSGuid],
		[CityId],
		[SchemeName],
		[TargetObject],
		[MemberUnique],
		[Description],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [DGSGuid],
		@CityId,
		[SchemeName],
		[TargetObject],
		[MemberUnique],
		[Description],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [SuZhou_Fandian].[dbo].[DivisionGroupScheme]
	;
	
	--Enum	PASS
	
	--FreightGroup
	PRINT 'Table [dbo].[FreightGroup] ...';
	INSERT INTO [dbo].[FreightGroup] (
		[FrtGrpGuid],
		[FrtGrpName],
		[IsDefault],
		[AddTime],
		[AddUser]
	)
	SELECT [FrtGrpGuid],
		[FrtGrpName],
		[IsDefault],
		[AddTime],
		[AddUser]
	FROM [SuZhou_Fandian].[dbo].[FreightGroup]
	WHERE [FrtGrpGuid] NOT IN (
		SELECT [FrtGrpGuid]
			FROM [dbo].[FreightGroup]
	)
	;
	--Freight
	PRINT 'Table [dbo].[Freight] ...';
	INSERT INTO [dbo].[Freight] (
		[FreightGuid],
		[FrtGrpGuid],
		[TransportMethod],
		[Distance],
		[Freight],
		[AddTime],
		[AddUser]
	)
	SELECT [FreightGuid],
		[FrtGrpGuid],
		[TransportMethod],
		[Distance],
		[Freight],
		[AddTime],
		[AddUser]
		FROM [SuZhou_Fandian].[dbo].[FreightRate]
	;
	--IssueLastVersion
	PRINT 'Table [dbo].[IssueLastVersion] ...';
	INSERT INTO [dbo].[IssueLastVersion] (
		[OrderGuid],
		[CityId],
		[IssueGuid]
	)
	SELECT [OrderGuid],
			@CityId,
			[IssueGuid]
			FROM [SuZhou_Fandian].[dbo].[IssueLastVersion]
	;
	--ItemUnit
	PRINT 'Table [dbo].[ItemUnit] ...';
	INSERT INTO [dbo].[ItemUnit] (
		[UnitGuid],
		[UnitName],
		[DecimalDigits],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [UnitGuid],
		[UnitName],
		[DecimalDigits],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [SuZhou_Fandian].[dbo].[ItemUnit]
	WHERE [UnitName] NOT IN (
		SELECT [UnitName]
			FROM [dbo].[ItemUnit]
	)
	;
	--Item
	PRINT 'Table [dbo].[Item] ...';
	INSERT INTO [dbo].[Item] (
		[ItemGuid],
		[CityId],
		[ItemId],
		[ItemName],
		[VendorGuid],
		[SrvCmbGuid],
		[CtgGroupGuid],
		[SortGroupGuid],
		[UnitGuid],
		[UnitPrice],
		[MinOrderQty],
		[SettleDiscount],
		[ItemQty],
		[BoxQty],
		[BoxUnitPrice],
		[IsSetMeal],
		[InputCode],
		[Description],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT i.[ItemGuid],
		@CityId,
		i.[ItemId],
		i.[ItemName],
		i.[VendorGuid],
		@SrvCmbGuidSZ,
		CASE WHEN ncg.[CtgGroupGuid] IS NULL THEN
			cg.[CtgGroupGuid]
		ELSE 
			ncg.[CtgGroupGuid]
		END,
		i.[SortGroupGuid],
		iu2.[UnitGuid],
		i.[UnitPrice],
		i.[MinOrderQty],
		i.[SettleDiscount],
		i.[ItemQty],
		i.[BoxQty],
		i.[BoxUnitPrice],
		@IsSetMeal,
		i.[InputCode],
		i.[Description],
		i.[Remark],
		i.[Disabled],
		i.[AddTime],
		i.[AddUser]
		FROM [SuZhou_Fandian].[dbo].[Item] AS i
			INNER JOIN [SuZhou_FanDian].[dbo].[CategoryGroup] AS cg
				ON cg.[CtgGroupGuid]=i.[CtgGroupGuid]
			LEFT JOIN [dbo].[CategoryGroup] AS ncg
				ON ncg.[CtgGroupName]=cg.[CtgGroupName]
			INNER JOIN [SuZhou_Fandian].[dbo].[Vendor] AS v
				ON i.[VendorGuid]=v.[VendorGuid]
			INNER JOIN [SuZhou_Fandian].[dbo].[ItemUnit] AS iu
				ON iu.[UnitGuid]=i.[UnitGuid]
			INNER JOIN (
					SELECT u.[UnitGuid], u.[UnitName]
						FROM [dbo].[ItemUnit] AS u
							INNER JOIN [SuZhou_Fandian].[dbo].[ItemUnit] AS u2
								ON u2.[UnitName]=u.[UnitName]
				) AS iu2
				ON iu2.[UnitName]=iu.[UnitName]
	WHERE v.[VendorName] NOT LIKE '%HX%' AND i.[ItemGuid] NOT IN (
		SELECT [ItemGuid]
			FROM [dbo].[Item]
	)
	;
	--Item SZ SIP
	PRINT 'Table [dbo].[Item] ...';
	INSERT INTO [dbo].[Item] (
		[ItemGuid],
		[CityId],
		[ItemId],
		[ItemName],
		[VendorGuid],
		[SrvCmbGuid],
		[CtgGroupGuid],
		[SortGroupGuid],
		[UnitGuid],
		[UnitPrice],
		[MinOrderQty],
		[SettleDiscount],
		[ItemQty],
		[BoxQty],
		[BoxUnitPrice],
		[IsSetMeal],
		[InputCode],
		[Description],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT i.[ItemGuid],
		@CityId,
		i.[ItemId],
		i.[ItemName],
		i.[VendorGuid],
		@SrvCmbGuidSZSIP,
		CASE WHEN ncg.CtgGroupGuid IS NULL THEN
			cg.CtgGroupGuid
		ELSE 
			ncg.CtgGroupGuid
		END,
		i.[SortGroupGuid],
		iu2.[UnitGuid],
		i.[UnitPrice],
		i.[MinOrderQty],
		i.[SettleDiscount],
		i.[ItemQty],
		i.[BoxQty],
		i.[BoxUnitPrice],
		@IsSetMeal,
		i.[InputCode],
		i.[Description],
		i.[Remark],
		i.[Disabled],
		i.[AddTime],
		i.[AddUser]
		FROM [SuZhou_Fandian].[dbo].[Item] AS i
			INNER JOIN [SuZhou_FanDian].[dbo].[CategoryGroup] AS cg
				ON cg.[CtgGroupGuid]=i.[CtgGroupGuid]
			LEFT JOIN [dbo].[CategoryGroup] AS ncg
				ON ncg.[CtgGroupName]=cg.[CtgGroupName]
			INNER JOIN [SuZhou_Fandian].[dbo].[Vendor] AS v
				ON i.[VendorGuid]=v.[VendorGuid]
			INNER JOIN [SuZhou_Fandian].[dbo].[ItemUnit] AS iu
				ON iu.[UnitGuid]=i.[UnitGuid]
			INNER JOIN (
					SELECT u.[UnitGuid], u.[UnitName]
						FROM [dbo].[ItemUnit] AS u
							INNER JOIN [SuZhou_Fandian].[dbo].[ItemUnit] AS u2
								ON u2.[UnitName]=u.[UnitName]
				) AS iu2
				ON iu2.[UnitName]=iu.[UnitName]
	WHERE v.[VendorName] LIKE '%HX%' AND i.[ItemGuid] NOT IN (
		SELECT [ItemGuid]
			FROM [dbo].[Item]
	)
	;
	--ItemProperty
	PRINT 'Table [dbo].[ItemProperty] ...';
	INSERT INTO [dbo].[ItemProperty] (
		[PropGuid],
		[PropName],
		[PropValue],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [PropGuid],
			[PropName],
			[PropValue],
			[Disabled],
			[AddTime],
			[AddUser]
			FROM [SuZhou_Fandian].[dbo].[ItemProperty]
	;
	--ItemSoldOut	PASS
	--LatlngAll	PASS
	
	--LatlngSimple	PASS
	
	--NewWebSales PASS
	
	--NumberSequence	PASS?
	
	--Order
	PRINT 'Table [dbo].[Order] ...';
	INSERT INTO [dbo].[Order] (
		[OrderGuid],
		[CityId],
		[VersionId],
		[OrderId],
		[StatusId],
		[IsClosed],
		[IsCanceled],
		[SalesGuid],
		[VendorGuid],
		[VendorId],
		[VendorName],
		[VendorCoord],
		[Variable],
		[ItemCount],
		[ItemAmount],
		[BoxQty],
		[BoxAmount],
		[SumAmount],
		[Distance],
		[Freight],
		[FreightOrigin],
		[TotalAmount],
		[PaymentMethod],
		[TransportMethod],
		[ReqTimeStart],
		[TimeDirection],
		[ReqTimeEnd],
		[Remark],
		[CustChanged],
		[InfoChanged],
		[ItemChanged],
		[InitTime],
		[CreatedTime],
		[AddTime],
		[AddUser],
		[ModifiedTime],
		[ModifiedUser]
	)
	SELECT o.OrderGuid,
			@CityId,
			ov.VersionId,
			o.OrderId,
			ov.StatusId,
			ov.IsClosed,
			ov.IsCanceled,
			o.SalesGuid,
			p.VendorGuid,
			p.VendorId,
			p.VendorName,
			p.Coordinate,
			p.Variable,
			oiv.ItemCount,
			oiv.ItemAmount,
			oiv.BoxQty,
			oiv.BoxAmount,
			oiv.SumAmount,
			fv.Distance,
			fv.Freight,
			fv.FreightOrigin,
			ov.TotalAmount,
			fv.PaymentMethod,
			fv.TransportMethod,
			sv.ReqTimeStart,
			sv.TimeDirection,
			sv.ReqTimeEnd,
			fv.Remark,
			0,
			ov.FreightChanged,
			0,
			o.AddTime,
			o.AddTime,
			o.AddTime,
			o.AddUser,
			NULL,
			NULL
		FROM [SuZhou_Fandian].[dbo].[Order] AS o
			INNER JOIN [SuZhou_Fandian].[dbo].[OrderVersion] AS ov
				ON ov.OrderGuid=o.OrderGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[OrderLastVersion] AS olv
				ON olv.OrdVerGuid=ov.OrdVerGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[OrderItemVersion] AS oiv
				ON oiv.OIVGuid=ov.OIVGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[SalesVersion] AS sv
				ON sv.SalesVerGuid=ov.SalesVerGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[Sales] AS s
				ON s.SalesGuid=o.SalesGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[FreightVersion] AS fv
				ON fv.FrtVerGuid=ov.FrtVerGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[Purchase] AS p
				ON p.PurchGuid=o.PurchGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[Vendor] AS v
				ON v.VendorGuid=p.VendorGuid
	;
	DELETE FROM [dbo].[OrderVersion] FROM [dbo].[OrderVersion] AS ov
		INNER JOIN [dbo].[Order] AS o
			ON ov.OrderGuid=o.OrderGuid
	WHERE ov.[VersionId]=o.[VersionId];
	UPDATE [dbo].[Order] SET [VendorName]=SUBSTRING([VendorName], 5, 9999)
		WHERE [VendorName] LIKE '%HX%';
	--OrderCancel
	PRINT 'Table [dbo].[OrderCancel] ...';
	INSERT INTO [dbo].[OrderCancel] (
		[OrderGuid],
		[CityId],
		[CancelGuid],
		[Remark],
		[AddUser],
		[AddTime]
	)
	SELECT oc.[OrderGuid],
			@CityId,
			oc.[CancelGuid],
			oc.[Remark],
			oc.[AddUser],
			oc.[AddTime]
			FROM [SuZhou_Fandian].[dbo].[OrderCancel] AS oc
				INNER JOIN [SuZhou_Fandian].[dbo].[CancelReason] AS cr
					ON cr.[CancelGuid]=oc.[CancelGuid]
		WHERE cr.[Reason] NOT IN (
			SELECT [Reason]
				FROM [dbo].[CancelReason]
		) AND oc.[CancelGuid] NOT IN (
			SELECT [CancelGuid]
				FROM [OrderCancel]
		)
	;
	INSERT INTO [dbo].[OrderCancel] (
		[OrderGuid],
		[CityId],
		[CancelGuid],
		[Remark],
		[AddUser],
		[AddTime]
	)
	SELECT oc.[OrderGuid],
			@CityId,
			ncr.[CancelGuid],
			oc.[Remark],
			oc.[AddUser],
			oc.[AddTime]
			FROM [SuZhou_Fandian].[dbo].[OrderCancel] AS oc
				INNER JOIN [SuZhou_Fandian].[dbo].[CancelReason] AS cr
					ON cr.[CancelGuid]=oc.[CancelGuid]
				INNER JOIN [dbo].[CancelReason] AS ncr
					ON ncr.[Reason]=cr.[Reason]
		WHERE cr.[Reason] IN (
			SELECT [Reason]
				FROM [dbo].[CancelReason]
		) AND oc.[CancelGuid] NOT IN (
			SELECT [CancelGuid]
				FROM [OrderCancel]
		)
	--OrderItem
	PRINT 'Table [dbo].[OrderItem] ...';
	INSERT INTO [dbo].[OrderItem] (
		[OrdItemGuid],
		[CityId],
		[OrderGuid],
		[LineIndex],
		[ItemGuid],
		[ItemId],
		[ItemName],
		[ItemPrice],
		[ItemQty],
		[MinOrderQty],
		[ItemUnit],
		[ItemAmount],
		[BoxQty],
		[BoxRatioQty],
		[ItemRatioQty],
		[BoxPrice],
		[BoxAmount],
		[TotalAmount],
		[ItemReq],
		[Remark],
		[ItemPriceOrigin],
		[ItemPriceLastModified],
		[ItemQtyLastModified],
		[StatusId],
		[IsClosed],
		[IsCanceled],
		[CancelGuid],
		[AddUser],
		[AddTime]
	)
	SELECT oi.[OrdItemGuid],
			@CityId,
			oi.[OrderGuid],
			oi.[LineIndex],
			oi.[ItemGuid],
			oi.[ItemId],
			oi.[ItemName],
			oi.[ItemPrice],
			oi.[ItemQty],
			oi.[MinOrderQty],
			oi.[ItemUnit],
			oi.[ItemAmount],
			oi.[BoxQty],
			oi.[BoxRatioQty],
			oi.[ItemRatioQty],
			oi.[BoxPrice],
			oi.[BoxAmount],
			oi.[TotalAmount],
			oi.[ItemReq],
			oi.[Remark],
			oi.[ItemPriceOrigin],
			oi.[ItemPriceLastModified],
			oi.[ItemQtyLastModified],
			oi.[StatusId],
			oi.[IsClosed],
			oi.[IsCanceled],
			oi.[CancelGuid],
			oi.[AddUser],
			oi.[AddTime]
		FROM [SuZhou_Fandian].[dbo].[Order] AS o
			INNER JOIN [SuZhou_Fandian].[dbo].[OrderVersion] AS ov
				ON ov.OrderGuid=o.OrderGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[OrderLastVersion] AS olv
				ON olv.OrdVerGuid=ov.OrdVerGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[FreightVersion] AS fv
				ON fv.FrtVerGuid=ov.FrtVerGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[Purchase] AS p
				ON p.PurchGuid=o.PurchGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[OrderItemVersion] AS oiv
				ON oiv.OIVGuid=ov.OIVGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[OIV_Item] AS oivi
				ON oivi.OIVGuid=oiv.OIVGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[OrderItem] AS oi
				ON oi.OrdItemGuid=oivi.OrdItemGuid
	;
	--OrderItemVersion
	PRINT 'Table [dbo].[OrderItemVersion] ...';
	INSERT INTO [dbo].[OrderItemVersion] (
		[OrdItemGuid],
		[CityId],
		[OrderGuid],
		[LineIndex],
		[ItemGuid],
		[ItemId],
		[ItemName],
		[ItemPrice],
		[ItemQty],
		[MinOrderQty],
		[ItemUnit],
		[ItemAmount],
		[BoxQty],
		[BoxRatioQty],
		[ItemRatioQty],
		[BoxPrice],
		[BoxAmount],
		[TotalAmount],
		[ItemReq],
		[Remark],
		[ItemPriceOrigin],
		[ItemPriceLastModified],
		[ItemQtyLastModified],
		[StatusId],
		[IsClosed],
		[IsCanceled],
		[CancelGuid],
		[AddUser],
		[AddTime]
	)
	SELECT DISTINCT oi.OrdItemGuid,
		@CityId,
		oi.OrderGuid,
		oi.LineIndex,
		oi.ItemGuid,
		oi.ItemId,
		oi.ItemName,
		oi.ItemPrice,
		oi.ItemQty,
		oi.MinOrderQty,
		oi.ItemUnit,
		oi.ItemAmount,
		oi.BoxQty,
		oi.BoxRatioQty,
		oi.ItemRatioQty,
		oi.BoxPrice,
		oi.BoxAmount,
		oi.TotalAmount,
		oi.ItemReq,
		oi.Remark,
		oi.ItemPriceOrigin,
		oi.ItemPriceLastModified,
		oi.ItemQtyLastModified,
		oi.StatusId,
		oi.IsClosed,
		oi.IsCanceled,
		oi.CancelGuid,
		oi.AddUser,
		oi.AddTime
	FROM [SuZhou_Fandian].[dbo].[OrderVersion] AS ov
		INNER JOIN [SuZhou_Fandian].[dbo].[OIV_Item] AS oivi
			ON oivi.OIVGuid=ov.OIVGuid
		INNER JOIN [SuZhou_Fandian].[dbo].[OrderItem] AS oi
			ON oi.[OrdItemGuid]=oivi.OrdItemGuid
		INNER JOIN [SuZhou_Fandian].[dbo].[Order] AS o
			ON o.[OrderGuid]=ov.[OrderGuid]
	WHERE ov.[OrdVerGuid] NOT IN (
		SELECT ov.OrdVerGuid
			FROM [SuZhou_Fandian].[dbo].[Order] AS o
				INNER JOIN [SuZhou_Fandian].[dbo].[OrderVersion] AS ov
					ON ov.[OrderGuid]=o.[OrderGuid]
				INNER JOIN [SuZhou_Fandian].[dbo].[OrderLastVersion] AS olv
					ON olv.[OrdVerGuid]=ov.[OrdVerGuid]
	)
	;
	--OrdeLoss
	PRINT 'Table [dbo].[OrderLoss] ...';
	INSERT INTO [dbo].[OrderLoss] (
		[LossGuid],
		[CityId],
		[OrderGuid],
		[LossType],
		[LossAmount],
		[Reason],
		[Handling],
		[Handler],
		[Remark],
		[AddTime],
		[AddUser]
	)
	SELECT [LossGuid],
			@CityId,
			[OrderGuid],
			[LossType],
			[LossAmount],
			[Reason],
			[Handling],
			[Handler],
			[Remark],
			[AddTime],
			[AddUser]
		FROM [SuZhou_Fandian].[dbo].[OrderLoss]
	;
	--OrderOnlinePay
	PRINT 'Table [dbo].[OrderOnlinePay] ...';
	INSERT INTO [dbo].[OrderOnlinePay] (
		[OrderGuid],
		[PayedMoney],
		[AddTime],
		[PayedVia]
	)
	SELECT [OrderGuid],
			[PayedMoney],
			[AddTime],
			[PayedVia]
		FROM [SuZhou_Fandian].[dbo].[OrderOnlinePay]
	;
	--OrderPayment
	PRINT 'Table [dbo].[OrderPayment] ...';
	INSERT INTO [dbo].[OrderPayment] (
		[PaymentGuid],
		[OrderGuid],
		[Hash],
		[AddTime],
		[BankApi],
		[BankId],
		[PaidMoney],
		[CallbackSign]
	)
	SELECT [PaymentGuid],
			[OrderGuid],
			[Hash],
			[AddTime],
			[BankApi],
			[BankId],
			[PaidMoney],
			[CallbackSign]
		FROM [SuZhou_Fandian].[dbo].[OrderPayment]
		WHERE [Hash] NOT IN (
			SELECT [Hash]
				FROM [dbo].[OrderPayment]
		)
	;
	--OrderStatus	PASS
	
	--OrderStatusLog
	PRINT 'Table [dbo].[OrderStatusLog] ...';
	INSERT INTO [dbo].[OrderStatusLog] (
		[StatusLogGuid],
		[CityId],
		[OrderGuid],
		[StatusId],
		[Remark],
		[AddTime],
		[AddUser]
	)
	SELECT [StatusLogGuid],
			@CityId,
			[OrderGuid],
			[StatusId],
			[Remark],
			[AddTime],
			[AddUser]
		FROM [SuZhou_Fandian].[dbo].[OrderStatusLog]
	;
	--OrderVersionItem
	PRINT 'Table [dbo].[OrderVersionItem] ...';
	INSERT INTO [dbo].[OrderVersionItem] (
		[CityId],
		[OVIGuid],
		[OrdItemGuid]
	)
	SELECT @CityId,
			[OIVGuid],
			[OrdItemGuid]
		FROM [SuZhou_Fandian].[dbo].[OIV_Item]
	;
	--OrderVersion
	PRINT 'Table [dbo].[OrderVersion] ...';
	INSERT INTO [dbo].[OrderVersion] (
		[OrdVerGuid],
		[CityId],
		[OrderGuid],
		[VersionId],
		[OrderId],
		[StatusId],
		[IsClosed],
		[IsCanceled],
		[SalesGuid],
		[SalesVerGuid],
		[VendorGuid],
		[VendorId],
		[VendorName],
		[VendorCoord],
		[Variable],
		[ItemCount],
		[ItemAmount],
		[BoxQty],
		[BoxAmount],
		[SumAmount],
		[Distance],
		[Freight],
		[FreightOrigin],
		[TotalAmount],
		[PaymentMethod],
		[TransportMethod],
		[ReqTimeStart],
		[TimeDirection],
		[ReqTimeEnd],
		[Remark],
		[CustChanged],
		[InfoChanged],
		[ItemChanged],
		[InitTime],
		[CreatedTime],
		[AddTime],
		[AddUser],
		[OVIGuid]
	)
	SELECT ov.OrdVerGuid,
			@CityId,
			ov.OrderGuid,
			ov.VersionId,
			o.OrderId,
			ov.StatusId,
			ov.IsClosed,
			ov.IsCanceled,
			o.SalesGuid,
			ov.SalesVerGuid,
			p.VendorGuid,
			p.VendorId,
			p.VendorName,
			p.Coordinate,
			p.Variable,
			oiv.ItemCount,
			oiv.ItemAmount,
			oiv.BoxQty,
			oiv.BoxAmount,
			oiv.SumAmount,
			fv.Distance,
			fv.Freight,
			fv.FreightOrigin,
			ov.TotalAmount,
			fv.PaymentMethod,
			fv.TransportMethod,
			sv.ReqTimeStart,
			sv.TimeDirection,
			sv.ReqTimeEnd,
			fv.Remark,
			ov.CustChanged,
			ov.FreightChanged,
			ov.ItemChanged,
			ov.AddTime,
			ov.AddTime,
			ov.AddTime,
			ov.AddUser,
			oiv.OIVGuid
		FROM [SuZhou_Fandian].[dbo].[OrderVersion] AS ov
			INNER JOIN [SuZhou_Fandian].[dbo].[Order] AS o
				ON o.OrderGuid=ov.OrderGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[OrderItemVersion] AS oiv
				ON oiv.OIVGuid=ov.OIVGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[Purchase] AS p
				ON p.PurchGuid=o.PurchGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[FreightVersion] AS fv
				ON fv.FrtVerGuid=ov.FrtVerGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[SalesVersion] AS sv
				ON sv.SalesVerGuid=ov.SalesVerGuid
			INNER JOIN (
				SELECT ov.[OrderGuid], ov.[VersionId], MAX(ov.AddTime) AS [AddTime]
				FROM [WuXi_Fandian].[dbo].[Order] AS o
					INNER JOIN [WuXi_Fandian].[dbo].[OrderVersion] AS ov
						ON ov.[OrderGuid]=o.[OrderGuid]
				GROUP BY ov.[VersionId], ov.[OrderGuid]
			) AS mov
				ON mov.OrderGuid=ov.OrderGuid AND mov.VersionId=ov.VersionId AND ov.AddTime=mov.AddTime
		WHERE [OrdVerGuid] NOT IN (
			SELECT [OrdVerGuid]
				FROM [dbo].[OrderVersion]
		)
	;
	--PermGroup	PASS
	
	--PermGroupRights	PASS
	
	--Region
	PRINT 'Table [dbo].[Region] ...';
	INSERT INTO [dbo].[Region] (
		[RegionGuid],
		[RegionId],
		[RegionName],
		[Level],
		[RealLevel],
		[ParentRegion],
		[CallingCode],
		[ZipCode],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [RegionGuid],
			[RegionId],
			[RegionName],
			[Level],
			[RealLevel],
			[ParentRegion],
			[CallingCode],
			[ZipCode],
			[Disabled],
			[AddTime],
			[AddUser]
		FROM [SuZhou_Fandian].[dbo].[Region]
		WHERE [RegionGuid] NOT IN (
			SELECT [RegionGuid]
				FROM [dbo].[Region]
		)
	;
	--RegionLevel	PASS
	
	--Rights	PASS
	
	--Sales
	PRINT 'Table [dbo].[Sales] ...';
	DECLARE SZSalesCursor CURSOR
		FOR SELECT DISTINCT o.SalesGuid, 
			@CityId,
			sv.VersionId,
			@CityGuid,
			@AreaGuidSZ,
			s.SalesSource,
			@SrvGrpGuidSZ,
			--sv.SrvGrpGuid,
			@SrvGrpNameSZ,
			--sv.SrvGrpName,
			@ServiceGuidSZ,
			--sv.ServiceGuid,
			@ServiceNameSZ,
			--sv.ServiceName,
			s.CustGuid,
			sv.CustName,
			sv.Category,
			s.IsNewCust,
			s.PhoneGuid,
			s.CallPhone,
			sv.AddressGuid,
			sv.CustAddress,
			sv.CoordGuid,
			sv.CoordName,
			--geography::STGeomFromText('POINT('+co.Longitude+' '+co.Latitude+')', 4326),
			sv.ReqDate,
			@Paid,
			sv.Invoice,
			sv.CommonComment,
			sv.RequestRemark,
			sv.SalesAttribute,
			sv.AddTime,
			sv.AddTime,
			sv.AddUser
		FROM [SuZhou_Fandian].[dbo].[Order] AS o
			INNER JOIN [SuZhou_Fandian].[dbo].[OrderVersion] AS ov
				ON ov.OrderGuid=o.OrderGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[OrderLastVersion] AS olv
				ON olv.OrdVerGuid=ov.OrdVerGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[SalesVersion] AS sv
				ON sv.SalesVerGuid=ov.SalesVerGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[Sales] AS s
				ON s.SalesGuid=o.SalesGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[Purchase] AS p
				ON p.PurchGuid=o.PurchGuid
			INNER JOIN [SuZhou_Fandian].[dbo].[Vendor] AS v
				ON v.VendorGuid=p.VendorGuid
		WHERE v.VendorName NOT LIKE '%HX%' 
	OPEN SZSalesCursor
	FETCH NEXT FROM SZSalesCursor
		INTO 
		@sSalesGuid, @sCityId, @sVersionId, @sCityGuid, @sAreaGuidSZ, @sSalesSource, @sSrvGrpGuidSZ,
		@sSrvGrpNameSZ, @sServiceGuidSZ, @sServiceNameSZ, @sCustGuid, @sCustName, @sCategory, 
		@sIsNewCust, @sPhoneGuid, @sCallPhone, @sAddressGuid, @sCustAddress, 
		@sCoordGuid, @sCoordName, @sReqDate, @sPaid, @sInvoice, @sCommonComment, @sRequestRemark,
		@sSalesAttribute, @sAddTime, @sCreateTime, @sAddUser
	WHILE @@FETCH_STATUS=0
	BEGIN
			INSERT INTO [dbo].[Sales] (
				[SalesGuid],
				[CityId],
				[VersionId],
				[CityGuid],
				[AreaGuid],
				[SalesSource],
				[SrvGrpGuid],
				[SrvGrpName],
				[ServiceGuid],
				[ServiceName],
				[CustGuid],
				[CustName],
				[Category],
				[IsNewCust],
				[PhoneGuid],
				[CallPhone],
				[AddressGuid],
				[CustAddress],
				[CoordGuid],
				[CoordName],
				--[CoordValue],
				[ReqDate],
				[Paid],
				[Invoice],
				[CommonComment],
				[RequestRemark],
				[SalesAttribute],
				[CreatedTime],
				[AddTime],
				[AddUser]
			) 
			SELECT DISTINCT o.SalesGuid, 
			@CityId,
			sv.VersionId,
			@CityGuid,
			@AreaGuidSZ,
			s.SalesSource,
			@SrvGrpGuidSZ,
			--sv.SrvGrpGuid,
			@SrvGrpNameSZ,
			--sv.SrvGrpName,
			@ServiceGuidSZ,
			--sv.ServiceGuid,
			@ServiceNameSZ,
			--sv.ServiceName,
			s.CustGuid,
			sv.CustName,
			sv.Category,
			s.IsNewCust,
			s.PhoneGuid,
			s.CallPhone,
			sv.AddressGuid,
			sv.CustAddress,
			sv.CoordGuid,
			sv.CoordName,
			--geography::STGeomFromText('POINT('+co.Longitude+' '+co.Latitude+')', 4326),
			sv.ReqDate,
			@Paid,
			sv.Invoice,
			sv.CommonComment,
			sv.RequestRemark,
			sv.SalesAttribute,
			sv.AddTime,
			sv.AddTime,
			sv.AddUser
			FROM [SuZhou_Fandian].[dbo].[Order] AS o
				INNER JOIN [SuZhou_Fandian].[dbo].[OrderVersion] AS ov
					ON ov.OrderGuid=o.OrderGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[OrderLastVersion] AS olv
					ON olv.OrdVerGuid=ov.OrdVerGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[SalesVersion] AS sv
					ON sv.SalesVerGuid=ov.SalesVerGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[Sales] AS s
					ON s.SalesGuid=o.SalesGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[Purchase] AS p
					ON p.PurchGuid=o.PurchGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[Vendor] AS v
					ON v.VendorGuid=p.VendorGuid
		WHERE o.SalesGuid=@sSalesGuid AND o.SalesGuid NOT IN (
				SELECT TOP 1 [SalesGuid]
					FROM [dbo].[Sales]
			)
			PRINT 'Cursor Sales '+@sSalesGuid
			FETCH NEXT FROM SZSalesCursor
				INTO 
				@sSalesGuid, @sCityId, @sVersionId, @sCityGuid, @sAreaGuidSZ, @sSalesSource, @sSrvGrpGuidSZ,
				@sSrvGrpNameSZ, @sServiceGuidSZ, @sServiceNameSZ, @sCustGuid, @sCustName, @sCategory, 
				@sIsNewCust, @sPhoneGuid, @sCallPhone, @sAddressGuid, @sCustAddress, 
				@sCoordGuid, @sCoordName, @sReqDate, @sPaid, @sInvoice, @sCommonComment, @sRequestRemark,
				@sSalesAttribute, @sAddTime, @sCreateTime, @sAddUser		
		
	END
	CLOSE SZSalesCursor
	DEALLOCATE SZSalesCursor		
	--Sales SZ SIP
	PRINT 'Table [dbo].[Sales] ...';
	DECLARE SZSIPSalesCursor CURSOR
		FOR SELECT DISTINCT o.SalesGuid, 
			@CityId,
			sv.VersionId,
			@CityGuid,
			@AreaGuidSZSIP,
			s.SalesSource,
			@SrvGrpGuidSZSIP,
			--sv.SrvGrpGuid,
			@SrvGrpNameSZSIP,
			--sv.SrvGrpName,
			@ServiceGuidSZSIP,
			--sv.ServiceGuid,
			@ServiceNameSZSIP,
			--sv.ServiceName,
			s.CustGuid,
			sv.CustName,
			sv.Category,
			s.IsNewCust,
			s.PhoneGuid,
			s.CallPhone,
			sv.AddressGuid,
			sv.CustAddress,
			sv.CoordGuid,
			sv.CoordName,
			--geography::STGeomFromText('POINT('+co.Longitude+' '+co.Latitude+')', 4326),
			sv.ReqDate,
			@Paid,
			sv.Invoice,
			sv.CommonComment,
			sv.RequestRemark,
			sv.SalesAttribute,
			sv.AddTime,
			sv.AddTime,
			sv.AddUser
			FROM [SuZhou_Fandian].[dbo].[Order] AS o
				INNER JOIN [SuZhou_Fandian].[dbo].[OrderVersion] AS ov
					ON ov.OrderGuid=o.OrderGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[OrderLastVersion] AS olv
					ON olv.OrdVerGuid=ov.OrdVerGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[SalesVersion] AS sv
					ON sv.SalesVerGuid=ov.SalesVerGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[Sales] AS s
					ON s.SalesGuid=o.SalesGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[Purchase] AS p
					ON p.PurchGuid=o.PurchGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[Vendor] AS v
					ON v.VendorGuid=p.VendorGuid
		WHERE v.VendorName LIKE '%HX%' 
	OPEN SZSIPSalesCursor
	FETCH NEXT FROM SZSIPSalesCursor
		INTO 
		@sSalesGuid, @sCityId, @sVersionId, @sCityGuid, @sAreaGuidSZ, @sSalesSource, @sSrvGrpGuidSZ,
		@sSrvGrpNameSZ, @sServiceGuidSZ, @sServiceNameSZ, @sCustGuid, @sCustName, @sCategory, 
		@sIsNewCust, @sPhoneGuid, @sCallPhone, @sAddressGuid, @sCustAddress, 
		@sCoordGuid, @sCoordName, @sReqDate, @sPaid, @sInvoice, @sCommonComment, @sRequestRemark,
		@sSalesAttribute, @sAddTime, @sCreateTime, @sAddUser
	WHILE @@FETCH_STATUS=0
	BEGIN
			INSERT INTO [dbo].[Sales] (
				[SalesGuid],
				[CityId],
				[VersionId],
				[CityGuid],
				[AreaGuid],
				[SalesSource],
				[SrvGrpGuid],
				[SrvGrpName],
				[ServiceGuid],
				[ServiceName],
				[CustGuid],
				[CustName],
				[Category],
				[IsNewCust],
				[PhoneGuid],
				[CallPhone],
				[AddressGuid],
				[CustAddress],
				[CoordGuid],
				[CoordName],
				--[CoordValue],
				[ReqDate],
				[Paid],
				[Invoice],
				[CommonComment],
				[RequestRemark],
				[SalesAttribute],
				[CreatedTime],
				[AddTime],
				[AddUser]
			) 
			SELECT DISTINCT o.SalesGuid, 
			@CityId,
			sv.VersionId,
			@CityGuid,
			@AreaGuidSZSIP,
			s.SalesSource,
			@SrvGrpGuidSZSIP,
			--sv.SrvGrpGuid,
			@SrvGrpNameSZSIP,
			--sv.SrvGrpName,
			@ServiceGuidSZSIP,
			--sv.ServiceGuid,
			@ServiceNameSZSIP,
			--sv.ServiceName,
			s.CustGuid,
			sv.CustName,
			sv.Category,
			s.IsNewCust,
			s.PhoneGuid,
			s.CallPhone,
			sv.AddressGuid,
			sv.CustAddress,
			sv.CoordGuid,
			sv.CoordName,
			--geography::STGeomFromText('POINT('+co.Longitude+' '+co.Latitude+')', 4326),
			sv.ReqDate,
			@Paid,
			sv.Invoice,
			sv.CommonComment,
			sv.RequestRemark,
			sv.SalesAttribute,
			sv.AddTime,
			sv.AddTime,
			sv.AddUser
			FROM [SuZhou_Fandian].[dbo].[Order] AS o
				INNER JOIN [SuZhou_Fandian].[dbo].[OrderVersion] AS ov
					ON ov.OrderGuid=o.OrderGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[OrderLastVersion] AS olv
					ON olv.OrdVerGuid=ov.OrdVerGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[SalesVersion] AS sv
					ON sv.SalesVerGuid=ov.SalesVerGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[Sales] AS s
					ON s.SalesGuid=o.SalesGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[Purchase] AS p
					ON p.PurchGuid=o.PurchGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[Vendor] AS v
					ON v.VendorGuid=p.VendorGuid
		WHERE o.SalesGuid=@sSalesGuid AND o.SalesGuid NOT IN (
				SELECT TOP 1 [SalesGuid]
					FROM [dbo].[Sales]
			)
			PRINT 'Cursor Sales SIP '+@sSalesGuid
			FETCH NEXT FROM SZSIPSalesCursor
				INTO 
				@sSalesGuid, @sCityId, @sVersionId, @sCityGuid, @sAreaGuidSZ, @sSalesSource, @sSrvGrpGuidSZ,
				@sSrvGrpNameSZ, @sServiceGuidSZ, @sServiceNameSZ, @sCustGuid, @sCustName, @sCategory, 
				@sIsNewCust, @sPhoneGuid, @sCallPhone, @sAddressGuid, @sCustAddress, 
				@sCoordGuid, @sCoordName, @sReqDate, @sPaid, @sInvoice, @sCommonComment, @sRequestRemark,
				@sSalesAttribute, @sAddTime, @sCreateTime, @sAddUser		
		
	END
	CLOSE SZSIPSalesCursor
	DEALLOCATE SZSIPSalesCursor;	
	UPDATE [dbo].[Sales] 
		SET [CoordValue]=(
				SELECT [CoordValue]
					FROM [dbo].[Coordinate]
				WHERE [CoordGuid]=[dbo].[Sales].[CoordGuid]
			);
	--SalesAttribute
	PRINT 'Table [dbo].[SalesAttribute] ...';
	INSERT INTO [dbo].[SalesAttribute] (
		[AttrGuid],
		[AttributeName],
		[PaymentMethod],
		[Paid],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [AttrGuid],
			[AttributeName],
			[PaymentMethod],
			[Paid],
			[Remark],
			[Disabled],
			[AddTime],
			[AddUser]
			FROM [SuZhou_Fandian].[dbo].[SalesAttribute]
		WHERE [AttributeName] NOT IN (
			SELECT [AttributeName]
				FROM [dbo].[SalesAttribute]
		)
	;
	--SalesVersion
	PRINT 'Table [dbo].[SalesVersion] ...';
	INSERT INTO [dbo].[SalesVersion] (
		[SalesVerGuid],
		[CityId],
		[SalesGuid],
		[VersionId],
		[CityGuid],
		[AreaGuid],
		[SalesSource],
		[SrvGrpGuid],
		[SrvGrpName],
		[ServiceGuid],
		[ServiceName],
		[CustGuid],
		[IsNewCust],
		[PhoneGuid],
		[CallPhone],
		[CustName],
		[Category],
		[AddressGuid],
		[CustAddress],
		[CoordGuid],
		[CoordName],
		--[CoordValue],
		[ReqDate],
		[Invoice],
		[CommonComment],
		[RequestRemark],
		[SalesAttribute],
		[CreatedTime],
		[AddTime],
		[AddUser]
	)
	SELECT DISTINCT
			sv.SalesVerGuid,
			@CityId,
			sv.SalesGuid,
			sv.VersionId,
			@CityGuid,
			@AreaGuidSZ,
			s.SalesSource,
			@SrvGrpGuidSZ,
			@SrvGrpNameSZ,
			@ServiceGuidSZ,
			@ServiceNameSZ,
			s.CustGuid,
			s.IsNewCust,
			s.PhoneGuid,
			s.CallPhone,
			sv.CustName,
			sv.Category,
			sv.AddressGuid,
			sv.CustAddress,
			sv.CoordGuid,
			sv.CoordName,
			--sv.CoordValue,
			sv.ReqDate,
			sv.Invoice,
			sv.CommonComment,
			sv.RequestRemark,
			sv.SalesAttribute,
			s.CreatedTime,
			sv.AddTime,
			sv.AddUser
			FROM [SuZhou_Fandian].[dbo].[SalesVersion] AS sv
				INNER JOIN [SuZhou_Fandian].[dbo].[Sales] AS s
					ON s.SalesGuid=sv.SalesGuid
		WHERE sv.SalesGuid IN (
			SELECT DISTINCT
						sv.SalesVerGuid
						FROM [SuZhou_Fandian].[dbo].[SalesVersion] AS sv
							INNER JOIN [SuZhou_Fandian].[dbo].[Sales] AS s
								ON s.SalesGuid=sv.SalesGuid
							INNER JOIN [SuZhou_Fandian].[dbo].[Order] AS o
								ON o.SalesGuid=s.SalesGuid
							INNER JOIN [SuZhou_Fandian].[dbo].[OrderVersion] AS ov
								ON ov.SalesVerGuid=sv.SalesVerGuid AND ov.OrderGuid=o.OrderGuid
							INNER JOIN [SuZhou_Fandian].[dbo].[Purchase] AS p
								ON p.PurchGuid=o.PurchGuid
					WHERE p.[VendorName] NOT LIKE '%HX%'
					GROUP BY sv.SalesVerGuid		
		);
	--SalesVersion SZ SIP
	PRINT 'Table [dbo].[SalesVersion] ...';
	INSERT INTO [dbo].[SalesVersion] (
		[SalesVerGuid],
		[CityId],
		[SalesGuid],
		[VersionId],
		[CityGuid],
		[AreaGuid],
		[SalesSource],
		[SrvGrpGuid],
		[SrvGrpName],
		[ServiceGuid],
		[ServiceName],
		[CustGuid],
		[IsNewCust],
		[PhoneGuid],
		[CallPhone],
		[CustName],
		[Category],
		[AddressGuid],
		[CustAddress],
		[CoordGuid],
		[CoordName],
		--[CoordValue],
		[ReqDate],
		[Invoice],
		[CommonComment],
		[RequestRemark],
		[SalesAttribute],
		[CreatedTime],
		[AddTime],
		[AddUser]
	)
	SELECT DISTINCT
			sv.SalesVerGuid,
			@CityId,
			sv.SalesGuid,
			sv.VersionId,
			@CityGuid,
			@AreaGuidSZSIP,
			s.SalesSource,
			@SrvGrpGuidSZSIP,
			@SrvGrpNameSZSIP,
			@ServiceGuidSZSIP,
			@ServiceNameSZSIP,
			s.CustGuid,
			s.IsNewCust,
			s.PhoneGuid,
			s.CallPhone,
			sv.CustName,
			sv.Category,
			sv.AddressGuid,
			sv.CustAddress,
			sv.CoordGuid,
			sv.CoordName,
			--sv.CoordValue,
			sv.ReqDate,
			sv.Invoice,
			sv.CommonComment,
			sv.RequestRemark,
			sv.SalesAttribute,
			s.CreatedTime,
			sv.AddTime,
			sv.AddUser
			FROM [SuZhou_Fandian].[dbo].[SalesVersion] AS sv
				INNER JOIN [SuZhou_Fandian].[dbo].[Sales] AS s
					ON s.SalesGuid=sv.SalesGuid
		WHERE sv.SalesGuid IN (
			SELECT DISTINCT
						sv.SalesVerGuid
						FROM [SuZhou_Fandian].[dbo].[SalesVersion] AS sv
							INNER JOIN [SuZhou_Fandian].[dbo].[Sales] AS s
								ON s.SalesGuid=sv.SalesGuid
							INNER JOIN [SuZhou_Fandian].[dbo].[Order] AS o
								ON o.SalesGuid=s.SalesGuid
							INNER JOIN [SuZhou_Fandian].[dbo].[OrderVersion] AS ov
								ON ov.SalesVerGuid=sv.SalesVerGuid AND ov.OrderGuid=o.OrderGuid
							INNER JOIN [SuZhou_Fandian].[dbo].[Purchase] AS p
								ON p.PurchGuid=o.PurchGuid
					WHERE p.[VendorName] LIKE '%HX%'
					GROUP BY sv.SalesVerGuid		
		);
	UPDATE [dbo].[SalesVersion] 
		SET [CoordValue]=(
				SELECT [CoordValue]
					FROM [dbo].[Coordinate]
				WHERE [CoordGuid]=[dbo].[SalesVersion].[CoordGuid]
			);
	--SortGroup
	PRINT 'Table [dbo].[SortGroup] ...';
	INSERT INTO [dbo].[SortGroup] (
		[SortGroupGuid],
		[SortGroupName],
		[TargetObject],
		[SortIndex],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [SortGroupGuid],
			[SortGroupName],
			[TargetObject],
			[SortIndex],
			[Disabled],
			[AddTime],
			[AddUser]
			FROM [SuZhou_Fandian].[dbo].[SortGroup]
	;
	--User ????
	PRINT 'Table [dbo].[User] ...';
	INSERT INTO [dbo].[User] (
		[UserGuid],
		[UserId],
		[UserName],
		[Password],
		[SrvGrpGuid],
		[IsAdmin],
		[PermGroupGuid],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [UserGuid],
			[UserId],
			[UserName],
			[Password],
			[SrvGrpGuid],
			[IsAdmin],
			[PermGroupGuid],
			[Disabled],
			[AddTime],
			[AddUser]
			FROM [SuZhou_Fandian].[dbo].[User]
		WHERE [UserId] NOT IN (
			SELECT [UserId]
				FROM [dbo].[User]
		)
	;
	--Vendor
	PRINT 'Table [dbo].[Vendor] ...';
	INSERT INTO [dbo].[Vendor] (
		[VendorGuid],
		[CityId],
		[CityGuid],
		[AreaGuid],
		[RegionGuid],
		[VendorId],
		[VendorName],
		[ServiceStatus],
		[CtgGroupGuid],
		[SortGroupGuid],
		[CorpGuid],
		[SettleDiscount],
		[ProvideInvoice],
		[InvoiceMaxLimit],
		[TransportMethod],
		[IssueMethod],
		[OrderMinutes],
		[InputCode],
		[Description],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [VendorGuid],
			@CityId,
			@CityGuid,
			@AreaGuidSZ,
			[RegionGuid],
			[VendorId],
			[VendorName],
			[ServiceStatus],
			[CtgGroupGuid],
			[SortGroupGuid],
			[CorpGuid],
			[SettleDiscount],
			[ProvideInvoice],
			[InvoiceMaxLimit],
			[TransportMethod],
			@IssueMethod,
			@OrderMinutes,
			SUBSTRING([InputCode], 5, 999),
			[Description],
			[Remark],
			[Disabled],
			[AddTime],
			[AddUser]
			FROM [SuZhou_Fandian].[dbo].[Vendor]
		WHERE [VendorName] NOT LIKE '%HX%'
	;
	--Vendor SZ SIP
	PRINT 'Table [dbo].[Vendor] ...';
	INSERT INTO [dbo].[Vendor] (
		[VendorGuid],
		[CityId],
		[CityGuid],
		[AreaGuid],
		[RegionGuid],
		[VendorId],
		[VendorName],
		[ServiceStatus],
		[CtgGroupGuid],
		[SortGroupGuid],
		[CorpGuid],
		[SettleDiscount],
		[ProvideInvoice],
		[InvoiceMaxLimit],
		[TransportMethod],
		[IssueMethod],
		[OrderMinutes],
		[InputCode],
		[Description],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [VendorGuid],
			@CityId,
			@CityGuid,
			@AreaGuidSZSIP,
			[RegionGuid],
			[VendorId],
			SUBSTRING([VendorName], 5, 999),
			[ServiceStatus],
			[CtgGroupGuid],
			[SortGroupGuid],
			[CorpGuid],
			[SettleDiscount],
			[ProvideInvoice],
			[InvoiceMaxLimit],
			[TransportMethod],
			@IssueMethod,
			@OrderMinutes,
			SUBSTRING([InputCode], 5, 999),
			[Description],
			[Remark],
			[Disabled],
			[AddTime],
			[AddUser]
			FROM [SuZhou_Fandian].[dbo].[Vendor]
		WHERE [VendorName] LIKE '%HX%'
	;
	--VendorAddress
	PRINT 'Table [dbo].[VendorAddress] ...';
	INSERT INTO [dbo].[VendorAddress] (
		[VendorGuid],
		[CityId],
		[Address],
		[CoordType],
		[Longitude],
		[Latitude],
		[CoordValue],
		[Variable],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [VendorGuid],
			@CityId,
			[Address],
			[CoordType],
			[Longitude],
			[Latitude],
			[CoordValue],
			[Variable],
			[Disabled],
			[AddTime],
			[AddUser]
			FROM [SuZhou_Fandian].[dbo].[VendorAddress]
	;
	--VendorContactMethod
	PRINT 'Table [dbo].[VendorContactMethod] ...';
	INSERT INTO [dbo].[VendorContactMethod] (
		[VCMGuid],
		[CityId],
		[VendorGuid],
		[ContactMethod],
		[Number],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [VCMGuid],
			@CityId,
			[VendorGuid],
			[ContactMethod],
			[Number],
			[Disabled],
			[AddTime],
			[AddUser]
			FROM [SuZhou_Fandian].[dbo].[VendorContactMethod]
	;
	--VendorContactPerson
	PRINT 'Table [dbo].[VendorContactPerson] ...';
	INSERT INTO [dbo].[VendorContactPerson] (
		[VCPGuid],
		[CityId],
		[VendorGuid],
		[Name],
		[Title],
		[CellPhone],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [VCPGuid],
			@CityId,
			[VendorGuid],
			[Name],
			[Title],
			[CellPhone],
			[Disabled],
			[AddTime],
			[AddUser]
			FROM [SuZhou_Fandian].[dbo].[VendorContactPerson]
	;
	--VendorCorporate
	PRINT 'Table [dbo].[CancelReason] ...';
	INSERT INTO [dbo].[VendorCorporate] (
		[CorpGuid],
		[CityId],
		[CorpName],
		[AddTime],
		[AddUser]
	)
	SELECT [CorpGuid],
			@CityId,
			[CorpName],
			[AddTime],
			[AddUser]
			FROM [SuZhou_Fandian].[dbo].[VendorCorporate]
	;
	--VendorServiceTime
	PRINT 'Table [dbo].[VendorServiceTime] ...';
	INSERT INTO [dbo].[VendorServiceTime] (
		[VSTGuid],
		[CityId],
		[VendorGuid],
		[DayType],
		[StartTime],
		[EndTime],
		[SpanDays],
		[Hours],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [VSTGuid],
			@CityId,
			[VendorGuid],
			[DayType],
			[StartTime],
			[EndTime],
			[SpanDays],
			[Hours],
			[Disabled],
			[AddTime],
			[AddUser]
			FROM [SuZhou_Fandian].[dbo].[VendorServiceTime]
	;
	--W_Addressbook
	PRINT 'Table [dbo].[W_Addressbook] ...';
	UPDATE [dbo].[W_Addressbook] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_Addressbook] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_Addressbook] (CityId);
	
	INSERT INTO [dbo].[W_Addressbook] (
		[ABGuid],
		[CityId],
		[Title],
		[CustGuid],
		[IsDefault],
		[Address],
		[OrderNo],
		[CoordGuid],
		[CreateTime],
		[Contactor],
		[Phone]
	)
	SELECT NEWID(),
			@CityId,
			[Title],
			[CustGuid],
			[IsDefault],
			[Address],
			[OrderNo],
			[CoordGuid],
			[CreateTime],
			[Contactor],
			[Phone]
			FROM [SuZhou_Fandian].[dbo].[W_Addressbook]
	;
	--W_Article
	PRINT 'Table [dbo].[W_Article] ...';
	UPDATE [dbo].[W_Article] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_Article] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_Article] (CityId);

	INSERT INTO [dbo].[W_Article] (
		[CityId],
		[Title],
		[CategoryId],
		[Detail],
		[PubTime],
		[StartTime],
		[EndTime],
		[OrderNo],
		[Views],
		[PubFlag],
		[AttachHash],
		[FirstAttach],
		[RegionGuid]
	)
	SELECT @CityId,
			[Title],
			[CategoryId]+@CategoryOffset,
			[Detail],
			[PubTime],
			[StartTime],
			[EndTime],
			[OrderNo],
			[Views],
			[PubFlag],
			[AttachHash],
			[FirstAttach],
			[RegionGuid]
			FROM [SuZhou_Fandian].[dbo].[W_Article]
	;
	--W_ArticleCategory
	PRINT 'Table [dbo].[W_ArticleCategory] ...';
	UPDATE [dbo].[W_ArticleCategory] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_ArticleCategory] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_ArticleCategory] (CityId);
	
	SET IDENTITY_INSERT [dbo].[W_ArticleCategory] ON;
	INSERT INTO [dbo].[W_ArticleCategory] (
		[CityId],
		[CategoryId],
		[CategoryName],
		[OrderNo]
	)
	SELECT @CityId,
			[CategoryId]+@CategoryOffset,
			[CategoryName],
			[OrderNo]
			FROM [SuZhou_Fandian].[dbo].[W_ArticleCategory]
	;
	SET IDENTITY_INSERT [dbo].[W_ArticleCategory] OFF;
	--W_Attachment
	PRINT 'Table [dbo].[W_Attachment] ...';
	UPDATE [dbo].[W_Attachment] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_Attachment] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_Attachment] (CityId);
	
	INSERT INTO [dbo].[W_Attachment] (
		[CityId],
		[FileId],
		[Name],
		[MimeType],
		[Size],
		[UploadTime],
		[Description],
		[Uid],
		[ReadTimes],
		[Hash],
		[Ext],
		[OrderNo],
		[Usage],
		[Width],
		[Height]
	)
	SELECT @CityId,
			[FileId],
			[Name],
			[MimeType],
			[Size],
			[UploadTime],
			[Description],
			[Uid],
			[ReadTimes],
			[Hash],
			[Ext],
			[OrderNo],
			[Usage],
			[Width],
			[Height]
			FROM [SuZhou_Fandian].[dbo].[W_Attachment]
	;
	--W_AttachmentData
	PRINT 'Table [dbo].[W_AttachmentData] ...';
	INSERT INTO [dbo].[W_AttachmentData] (
		[FileId],
		[Data]
	)
	SELECT [FileId],
			[Data]
			FROM [SuZhou_Fandian].[dbo].[W_AttachmentData]
	;
	--W_BaiduPlaceLog
	PRINT 'Table [dbo].[W_BaiduPlaceLog] ...';
	UPDATE [dbo].[W_BaiduPlaceLog] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_BaiduPlaceLog] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_BaiduPlaceLog] (CityId);
	
	INSERT INTO [dbo].[W_BaiduPlaceLog] (
		[CityId],
		[Address],
		[Longitude],
		[Latitude],
		[AddTime],
		[Name],
		[Phone]
	)
	SELECT @CityId,
			[Address],
			[Longitude],
			[Latitude],
			[AddTime],
			[Name],
			[Phone]
			FROM [SuZhou_Fandian].[dbo].[W_BaiduPlaceLog]
	;
	--W_CoordForBaidu
	PRINT 'Table [dbo].[W_CoordForBaidu] ...';
	UPDATE [dbo].[W_CoordForBaidu] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_CoordForBaidu] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_CoordForBaidu] (CityId);
	
	INSERT INTO [dbo].[W_CoordForBaidu] (
		[CityId],
		[CoordGuid],
		[Longitude],
		[Latitude],
		[CoordValue]
	)
	SELECT @CityId,
			[CoordGuid],
			[Longitude],
			[Latitude],
			[CoordValue]
			FROM [SuZhou_Fandian].[dbo].[W_CoordForBaidu]
	;
	--W_FavoritedItems
	PRINT 'Table [dbo].[W_FavoritedItems] ...';
	UPDATE [dbo].[W_FavoritedItems] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_FavoritedItems] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_FavoritedItems] (CityId);
	
	INSERT INTO [dbo].[W_FavoritedItems] (
		[CityId],
		[CustGuid],
		[ItemGuid],
		[CreateTime]
	)
	SELECT @CityId,
			[CustGuid],
			[ItemGuid],
			[CreateTime]
			FROM [SuZhou_Fandian].[dbo].[W_FavoritedItems]
	;
	--W_FavoritedVendors
	PRINT 'Table [dbo].[W_FavoritedVendors] ...';
	UPDATE [dbo].[W_FavoritedVendors] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_FavoritedVendors] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_FavoritedVendors] (CityId);
	
	INSERT INTO [dbo].[W_FavoritedVendors] (
		[CityId],
		[CustGuid],
		[VendorGuid],
		[CreateTime]
	)
	SELECT @CityId,
			[CustGuid],
			[VendorGuid],
			[CreateTime]
			FROM [SuZhou_Fandian].[dbo].[W_FavoritedVendors]
	;
	--W_Feedback
	PRINT 'Table [dbo].[W_Feedback] ...';
	UPDATE [dbo].[W_Feedback] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_Feedback] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_Feedback] (CityId);
	
	INSERT INTO [dbo].[W_Feedback] (
		[CityId],
		[CustGuid],
		[Username],
		[CreateTime],
		[Content],
		[OrderNo],
		[DisplayFlag],
		[ReplyContent],
		[ReplyTime],
		[Title],
		[IpAddress],
		[RegionGuid]
	)
	SELECT @CityId,
			[CustGuid],
			[Username],
			[CreateTime],
			[Content],
			[OrderNo],
			[DisplayFlag],
			[ReplyContent],
			[ReplyTime],
			[Title],
			[IpAddress],
			[RegionGuid]
			FROM [SuZhou_Fandian].[dbo].[W_Feedback]
	;
	--W_ItemExtend
	PRINT 'Table [dbo].[W_ItemExtend] ...';
	UPDATE [dbo].[W_ItemExtend] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_ItemExtend] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_ItemExtend] (CityId);
	
	INSERT INTO [dbo].[W_ItemExtend] (
		[CityId],
		[ItemGuid],
		[HasLogo],
		[IsRec],
		[IsTuan],
		[Sales],
		[Detail],
		[Persisted],
		[LongTitle]
	)
	SELECT @CityId,
			[ItemGuid],
			[HasLogo],
			[IsRec],
			[IsTuan],
			[Sales],
			'',
			'',
			''
			FROM [SuZhou_Fandian].[dbo].[W_ItemExtend]
	;
	--W_OrderAnalysis	PASS
	
	--W_OrderAnnounce
	PRINT 'Table [dbo].[W_OrderAnnounce] ...';
	UPDATE [dbo].[W_OrderAnnounce] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_OrderAnnounce] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_OrderAnnounce] (CityId);
	
	INSERT INTO [dbo].[W_OrderAnnounce] (
		[CityId],
		[Content],
		[AddTime],
		[RegionGuid]
	)
	SELECT @CityId,
			[Content],
			[AddTime],
			[RegionGuid]
			FROM [SuZhou_Fandian].[dbo].[W_OrderAnnounce]
	;
	--W_OrderHash
	PRINT 'Table [dbo].[W_OrderHash] ...';
	UPDATE [dbo].[W_OrderHash] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_OrderHash] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_OrderHash] (CityId);
	
	INSERT INTO [dbo].[W_OrderHash] (
		[CityId],
		[OrderGuid],
		[Hash],
		[CreateTime],
		[PayMethod],
		[Payed],
		[BankApi],
		[BankId],
		[PayedMoney]
	)
	SELECT @CityId,
			[OrderGuid],
			[Hash],
			[CreateTime],
			[PayMethod],
			[Payed],
			[BankApi],
			[BankId],
			[PayedMoney]
			FROM [SuZhou_Fandian].[dbo].[W_OrderHash]
	;
	--W_ResetPasswordHash
	PRINT 'Table [dbo].[W_ResetPasswordHash] ...';
	UPDATE [dbo].[W_ResetPasswordHash] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_ResetPasswordHash] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_ResetPasswordHash] (CityId);
	
	INSERT INTO [dbo].[W_ResetPasswordHash] (
		[CityId],
		[Hash],
		[CustGuid],
		[CreateTime],
		[UsedTime]
	)
	SELECT @CityId,
			[Hash],
			[CustGuid],
			[CreateTime],
			[UsedTime]
			FROM [SuZhou_Fandian].[dbo].[W_ResetPasswordHash]
	;
	--W_SearchLogs
	PRINT 'Table [dbo].[W_SearchLogs] ...';
	UPDATE [dbo].[W_SearchLogs] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_SearchLogs] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_SearchLogs] (CityId);
	
	INSERT INTO [dbo].W_SearchLogs (
		[CityId],
		[Keywords],
		[CustGuid],
		[Results],
		[Timeline]
	)
	SELECT @CityId,
			[Keywords],
			[CustGuid],
			[Results],
			[Timeline]
			FROM [SuZhou_Fandian].[dbo].[W_SearchLogs]
	;
	--W_SystemVars
	PRINT 'Table [dbo].[W_SystemVars] ...';
	UPDATE [dbo].[W_SystemVars] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_SystemVars] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_SystemVars] (CityId);
	
	INSERT INTO [dbo].[W_SystemVars] (
		[CityId],
		[DataKey],
		[DataValue],
		[LastUpdate],
		[RegionGuid]
	)
	SELECT @CityId,
			[DataKey],
			[DataValue],
			[LastUpdate],
			[RegionGuid]
			FROM [SuZhou_Fandian].[dbo].[W_SystemVars]
	;
	--W_TencentConnectToken
	PRINT 'Table [dbo].[W_TencentConnectToken] ...';
	UPDATE [dbo].[W_TencentConnectToken] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_TencentConnectToken] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_TencentConnectToken] (CityId);
	
	INSERT INTO [dbo].[W_TencentConnectToken] (
		[CityId],
		[CustGuid],
		[Token],
		[OpenID],
		[LastUpdate]
	)
	SELECT @CityId,
			[CustGuid],
			[Token],
			[OpenID],
			[LastUpdate]
			FROM [SuZhou_Fandian].[dbo].[W_TencentConnectToken]
	;
	--W_Users
	PRINT 'Table [dbo].[W_Users] ...';
	UPDATE [dbo].[W_Users] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_Users] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_Users] (CityId);
	
	INSERT INTO [dbo].[W_Users] (
		[CityId],
		[CustGuid],
		[Username],
		[Password],
		[Avatar],
		[LastLogin],
		[Address],
		[Qq],
		[Msn],
		[Homepage]
	)
	SELECT @CityId,
			[CustGuid],
			[Username],
			[Password],
			[Avatar],
			[LastLogin],
			[Address],
			[Qq],
			[Msn],
			[Homepage]
			FROM [SuZhou_Fandian].[dbo].[W_Users]
	;
	--W_VendorExtend
	PRINT 'Table [dbo].[W_VendorExtend] ...';
	UPDATE [dbo].[W_VendorExtend] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_VendorExtend] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_VendorExtend] (CityId);
	
	INSERT INTO [dbo].[W_VendorExtend] (
		[CityId],
		[VendorGuid],
		[Views],
		[Favorites],
		[SmallLogo],
		[BigLogo],
		[AverageCost],
		[HotRate],
		[HasLogo],
		[IsRec],
		[IsIdxRec],
		[OrderNo]
	)
	SELECT @CityId,
			[VendorGuid],
			[Views],
			[Favorites],
			[SmallLogo],
			[BigLogo],
			[AverageCost],
			[HotRate],
			[HasLogo],
			[IsRec],
			[IsIdxRec],
			[OrderNo]
			FROM [SuZhou_Fandian].[dbo].[W_VendorExtend]
	;
	--W_WeiboOauthToken
	PRINT 'Table [dbo].[W_WeiboOauthToken] ...';
	UPDATE [dbo].[W_WeiboOauthToken] SET CityId=@WxCityId;
	ALTER TABLE [dbo].[W_WeiboOauthToken] ALTER COLUMN CityId NVARCHAR(20) NOT NULL;
	--CREATE NONCLUSTERED INDEX IX_CityId ON [dbo].[W_WeiboOauthToken] (CityId);
	
	INSERT INTO [dbo].[W_WeiboOauthToken] (
		[CityId],
		[CustGuid],
		[WeiboUid],
		[Token],
		[Expires],
		[LastUpdate]
	)
	SELECT @CityId,
			[CustGuid],
			[WeiboUid],
			[Token],
			[Expires],
			[LastUpdate]
			FROM [SuZhou_Fandian].[dbo].[W_WeiboOauthToken]
	;
	
--	COMMIT TRANSACTION;

--END TRY

--BEGIN CATCH
--	ROLLBACK TRANSACTION;
--END CATCH

--FIX CtgGroupGuid
PRINT 'Fix CtgGroupGuid ...';
DECLARE @OldCtgGuid NVARCHAR(50), @NewCtgGuid NVARCHAR(50);
DECLARE cCursor CURSOR
	FOR
		SELECT c.[CtgGuid], nc.[CtgGuid]
		FROM [SuZhou_FanDian].[dbo].[Category] AS c
			INNER JOIN [dbo].[Category] AS nc
				ON nc.[CtgName]=c.[CtgName]
		WHERE c.[CtgGuid] IN (
			SELECT DISTINCT cgm.[CtgGuid]
			FROM [dbo].[Vendor] AS v
				LEFT JOIN [dbo].[CategoryGroup] AS cg
					ON cg.[CtgGroupGuid]=v.[CtgGroupGuid]
				LEFT JOIN [dbo].[CategoryGroupMember] AS cgm
					ON cgm.[CtgGroupGuid]=v.[CtgGroupGuid]
				LEFT JOIN [dbo].[Category] AS c
					ON c.[CtgGuid]=cgm.[CtgGuid]
				LEFT JOIN [dbo].[CategoryStandard] AS cs
					ON cs.[CtgStdGuid]=c.[CtgStdGuid]
			
			where v.[CityId]=@CityId AND (
				cs.[CtgStdGuid] IS NULL
			) AND cgm.[CtgGuid] IS not null
		)
	OPEN cCursor
	FETCH NEXT FROM cCursor
		INTO
		@OldCtgGuid, @NewCtgGuid
	WHILE @@FETCH_STATUS=0
	BEGIN
		UPDATE [dbo].[CategoryGroupMember]
			SET [CtgGuid]=@NewCtgGuid
			WHERE [CtgGuid]=@OldCtgGuid
		PRINT 'Cursor Fix CtgGroupGuid '+@OldCtgGuid
		FETCH NEXT FROM cCursor
			INTO
			@OldCtgGuid, @NewCtgGuid
	END 
	CLOSE cCursor
	DEALLOCATE cCursor
	
--FIX CustGuid
PRINT 'Fix CustGuid ...';
DECLARE @OldCustGuid NVARCHAR(50), @NewCustGuid NVARCHAR(50);
DECLARE CustCursor CURSOR
		FOR
		SELECT ocp.[CustGuid] AS OldCustGuid,
			ncp.[CustGuid] AS NewCustGuid
			FROM [dbo].[CustomerPhone] AS ncp
				INNER JOIN [SuZhou_Fandian].[dbo].[CustomerPhone] AS ocp
					ON ocp.[PhoneNumber] = ncp.[PhoneNumber]
			WHERE ncp.[CityId]=@WxCityId
	OPEN CustCursor
	FETCH NEXT FROM CustCursor
		INTO 
		@OldCustGuid, @NewCustGuid
	WHILE @@FETCH_STATUS=0
	BEGIN
		PRINT 'Fix CustGuid '+@OldCustGuid
		--UPDATE [dbo].[BlackList] SET [CustGuid]=@NewCustGuid WHERE [CustGuid]=@OldCustGuid
		UPDATE [dbo].[CustomerAddress] SET [CustGuid]=@NewCustGuid WHERE [CustGuid]=@OldCustGuid
		UPDATE [dbo].[Sales] SET [CustGuid]=@NewCustGuid WHERE [CustGuid]=@OldCustGuid
		UPDATE [dbo].[SalesVersion] SET [CustGuid]=@NewCustGuid WHERE [CustGuid]=@OldCustGuid
		--UPDATE [dbo].[W_AddressBook] SET [CustGuid]=@NewCustGuid WHERE [CustGuid]=@OldCustGuid
		--UPDATE [dbo].[W_FavoritedItems] SET [CustGuid]=@NewCustGuid WHERE [CustGuid]=@OldCustGuid
		--UPDATE [dbo].[W_FavoritedVendors] SET [CustGuid]=@NewCustGuid WHERE [CustGuid]=@OldCustGuid
		--UPDATE [dbo].[W_Feedback] SET [CustGuid]=@NewCustGuid WHERE [CustGuid]=@OldCustGuid
		FETCH NEXT FROM CustCursor
			INTO 
			@OldCustGuid, @NewCustGuid
	END 

	CLOSE CustCursor
	DEALLOCATE CustCursor
	
	DELETE FROM [dbo].[Customer] WHERE [CityId]=@CityId AND [CustGuid] IN (
		SELECT ocp.[CustGuid] AS OldCustGuid
				FROM [dbo].[CustomerPhone] AS ncp
					INNER JOIN [SuZhou_Fandian].[dbo].[CustomerPhone] AS ocp
						ON ocp.[PhoneNumber] = ncp.[PhoneNumber]
				WHERE ncp.[CityId]=@WxCityId
	);
	DELETE FROM [dbo].[CustomerPhone] WHERE [CityId]=@CityId AND [CustGuid] IN (
		SELECT ocp.[CustGuid] AS OldCustGuid
				FROM [dbo].[CustomerPhone] AS ncp
					INNER JOIN [SuZhou_Fandian].[dbo].[CustomerPhone] AS ocp
						ON ocp.[PhoneNumber] = ncp.[PhoneNumber]
				WHERE ncp.[CityId]=@WxCityId
	);
	DELETE FROM [dbo].[W_ResetPasswordHash] WHERE [CityId]=@CityId AND [CustGuid] IN (
		SELECT ocp.[CustGuid] AS OldCustGuid
				FROM [dbo].[CustomerPhone] AS ncp
					INNER JOIN [SuZhou_Fandian].[dbo].[CustomerPhone] AS ocp
						ON ocp.[PhoneNumber] = ncp.[PhoneNumber]
				WHERE ncp.[CityId]=@WxCityId
	);
	DELETE FROM [dbo].[W_TencentConnectToken] WHERE [CityId]=@CityId AND [CustGuid] IN (
		SELECT ocp.[CustGuid] AS OldCustGuid
				FROM [dbo].[CustomerPhone] AS ncp
					INNER JOIN [SuZhou_Fandian].[dbo].[CustomerPhone] AS ocp
						ON ocp.[PhoneNumber] = ncp.[PhoneNumber]
				WHERE ncp.[CityId]=@WxCityId
	);
	DELETE FROM [dbo].[W_Users] WHERE [CityId]=@CityId AND [CustGuid] IN (
		SELECT ocp.[CustGuid] AS OldCustGuid
				FROM [dbo].[CustomerPhone] AS ncp
					INNER JOIN [SuZhou_Fandian].[dbo].[CustomerPhone] AS ocp
						ON ocp.[PhoneNumber] = ncp.[PhoneNumber]
				WHERE ncp.[CityId]=@WxCityId
	);
	DELETE FROM [dbo].[W_WeiboOauthToken] WHERE [CityId]=@CityId AND [CustGuid] IN (
		SELECT ocp.[CustGuid] AS OldCustGuid
				FROM [dbo].[CustomerPhone] AS ncp
					INNER JOIN [SuZhou_Fandian].[dbo].[CustomerPhone] AS ocp
						ON ocp.[PhoneNumber] = ncp.[PhoneNumber]
				WHERE ncp.[CityId]=@WxCityId
	);
	
--Fix Order/OrderVersion
UPDATE [dbo].[Order] SET [InfoChanged]=0 WHERE [VersionId]=0;
UPDATE [dbo].[OrderVersion] SET [InfoChanged]=0 WHERE [VersionId]=0;

--FIX 商圈
PRINT 'Fix 商圈 ...';
DECLARE @VendorGuid NVARCHAR(50), @BizArea NVARCHAR(50);
DECLARE baCursor CURSOR
	FOR
		SELECT v.VendorGuid, (r.RegionName+','+c.CtgName) AS [BizArea]
			FROM [SuZhou_Fandian].[dbo].[Vendor] AS v
				INNER JOIN [SuZhou_Fandian].[dbo].[Region] AS r
					ON r.RegionGuid=v.RegionGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[CategoryGroup] AS cg
					ON cg.CtgGroupGuid=v.CtgGroupGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[CategoryGroupMember] AS cgm
					ON cgm.CtgGroupGuid=cg.CtgGroupGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[Category] AS c
					ON c.CtgGuid=cgm.CtgGuid
				INNER JOIN [SuZhou_Fandian].[dbo].[CategoryStandard] AS cs
					ON cs.CtgStdGuid=c.CtgStdGuid
		WHERE cs.CtgStdName=N'商圈'
	OPEN baCursor
	FETCH NEXT FROM baCursor 
		INTO @VendorGuid, @BizArea
	WHILE @@FETCH_STATUS=0
	BEGIN
		PRINT 'Cursor Fix BizArea '+@BizArea
		UPDATE [dbo].[W_VendorExtend] SET [BizArea]=@BizArea WHERE [VendorGuid]=@VendorGuid
		FETCH NEXT FROM baCursor 
			INTO @VendorGuid, @BizArea
	END 
	CLOSE baCursor
	DEALLOCATE baCursor
	
--FIX FrtGrp
PRINT 'Fix FrtGroup ...';
UPDATE [dbo].[Service] SET [FrtGrpGuid]=@SZFrtGrpGuid WHERE [CityId]=@CityId;
	
--UPDATE CityId
PRINT 'Update CityId ...';
--UPDATE [dbo].[BlackList] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[BlackList] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[Coordinate] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[Coordinate] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[Customer] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[Customer] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[CustomerAddress] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[CustomerAddress] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[CustomerExpedite] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[CustomerExpedite] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[CustomerPhone] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[CustomerPhone] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[Deliveryman] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[Deliveryman] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[DeliverySchedule] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[DeliverySchedule] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[DeliveryScheduleTime] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[DeliveryScheduleTime] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[DeliveryShift] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[DeliveryShift] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[DeliveryShiftTime] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[DeliveryShiftTime] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[DistributionCenter] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[DistributionCenter] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[DivisionGroup] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[DivisionGroup] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[DivisionGroupMember] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[DivisionGroupMember] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[DivisionGroupScheme] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[DivisionGroupScheme] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[IssueLastVersion] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[IssueLastVersion] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[Item] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[Item] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[ItemSoldOut] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[ItemSoldOut] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[Order] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[Order] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[OrderCancel] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[OrderCancel] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[OrderIssue] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[OrderIssue] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[OrderItem] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[OrderItem] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[OrderItemVersion] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[OrderItemVersion] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[OrderLoss] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[OrderLoss] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[OrderSetMealItemVersion] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[OrderSetMealItemVersion] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[OrderStatusLog] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[OrderStatusLog] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[OrderVersion] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[OrderVersion] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[OrderVersionItem] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[OrderVersionItem] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[Sales] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[Sales] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[SalesVersion] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[SalesVersion] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[Service] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[Service] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[ServiceCombin] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[ServiceCombin] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[ServiceCombinMember] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[ServiceCombinMember] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[ServiceGroup] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[ServiceGroup] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[SetMealItem] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[SetMealItem] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[Vendor] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[Vendor] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[VendorAddress] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[VendorAddress] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[VendorContactMethod] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[VendorContactMethod] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[VendorContactPerson] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[VendorContactPerson] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[VendorCorporate] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[VendorCorporate] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_AddressBook] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_AddressBook] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_Article] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_Article] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;
UPDATE [dbo].[W_Article] SET [RegionGuid]=@NewSZRegionGuid WHERE [RegionGuid]=@OldSZRegionGuid;
UPDATE [dbo].[W_Article] SET [RegionGuid]=@NewSZSIPRegionGuid WHERE [RegionGuid]=@OldSZSIPRegionGuid;

--UPDATE [dbo].[W_Attachment] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_Attachment] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_BaiduPlaceLog] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_BaiduPlaceLog] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_CoordForBaidu] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_CoordForBaidu] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_FavoritedItems] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_FavoritedItems] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_FavoritedVendors] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_FavoritedVendors] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_Feedback] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_Feedback] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;
UPDATE [dbo].[W_Feedback] SET [RegionGuid]=@NewSZRegionGuid WHERE [RegionGuid]=@OldSZRegionGuid;
UPDATE [dbo].[W_Feedback] SET [RegionGuid]=@NewSZSIPRegionGuid WHERE [RegionGuid]=@OldSZSIPRegionGuid;

--UPDATE [dbo].[W_ItemExtend] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_ItemExtend] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_OrderAnnounce] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_OrderAnnounce] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;
UPDATE [dbo].[W_OrderAnnounce] SET [RegionGuid]=@NewSZRegionGuid WHERE [RegionGuid]=@OldSZRegionGuid;
UPDATE [dbo].[W_OrderAnnounce] SET [RegionGuid]=@NewSZSIPRegionGuid WHERE [RegionGuid]=@OldSZSIPRegionGuid;

--UPDATE [dbo].[W_OrderHash] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_OrderHash] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_ResetPasswordHash] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_ResetPasswordHash] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_SearchLogs] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_SearchLogs] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_SystemVars] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_SystemVars] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;
UPDATE [dbo].[W_SystemVars] SET [RegionGuid]=@NewSZRegionGuid WHERE [RegionGuid]=@OldSZRegionGuid;
UPDATE [dbo].[W_SystemVars] SET [RegionGuid]=@NewSZSIPRegionGuid WHERE [RegionGuid]=@OldSZSIPRegionGuid;

--UPDATE [dbo].[W_TencentConnectToken] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_TencentConnectToken] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_Users] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_Users] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_VendorExtend] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_VendorExtend] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_WeiboOauthToken] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_WeiboOauthToken] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;


--CLEAR SOME DATA
PRINT 'Clear some data ...';
DELETE FROM [dbo].[Region] WHERE [RegionGuid]='98445F49-656B-42ED-BA47-485E3EBE194A';

PRINT ''
PRINT ''
PRINT '=========================================================='
PRINT 'DONE'
PRINT '=========================================================='
PRINT ''
PRINT ''

