USE [wxfd_allcity];

--需要先在接单系统添加常州业务、业务组、业务组合等数据，并记下来相关Guid给导入脚本使用

--BEGIN TRANSACTION;

--无锡的城市id
DECLARE @WxCityId NVARCHAR(20)='wx';
--常州的城市id
DECLARE @CityId NVARCHAR(20)='cz';
--常州的城市Guid
DECLARE @CityGuid NVARCHAR(50)='71A6ABCA-0171-495C-BF73-547FA32AC2F1';
--是否是套餐
DECLARE @IsSetMeal BIT=0;
--常州城区配送中心Guid
DECLARE @DCGuidCZ NVARCHAR(50)='A3340446-927F-4E2E-941D-DA8EEFFEE5BC';
--常州城区的AreaGuid
DECLARE @AreaGuidCZ NVARCHAR(50)='0F1B6E8D-117B-4532-A945-46DE88190B0E';
--常州城区业务组Guid
DECLARE @SrvGrpGuidCZ NVARCHAR(50)='23627317-AE69-49FA-BFBB-724E36473B48';
--常州城区业务组名称
DECLARE @SrvGrpNameCZ NVARCHAR(50)='常州业务组';
--常州城区业务Guid
DECLARE @ServiceGuidCZ NVARCHAR(50)='2DDD25BC-A38E-4469-B0F2-CC1EAF301DDC';
--常州城区业务名称
DECLARE @ServiceNameCZ NVARCHAR(50)='普通';
--常州城区业务组组合Guid
DECLARE @SrvCmbGuidCZ NVARCHAR(50)='223C5385-ACA3-47C8-AA60-C09E193A2314';
--是否已付款
DECLARE @Paid BIT=0;
--文章分类的偏移量（解决自增字段的关联问题）
DECLARE @CategoryOffset INT=2000;
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

DECLARE @OldCZRegionGuid NVARCHAR(50)='117E6A72-3EA6-4CDB-973E-6CA98AA706B0';
DECLARE @NewCZRegionGuid NVARCHAR(50)='0F1B6E8D-117B-4532-A945-46DE88190B0E';

DECLARE @OldCZVIPCtgGroupGuid NVARCHAR(50)='D4DE128B-9E79-4A65-AB19-1F46437AB1F8';
DECLARE @OldCZNormalCtgGroupGuid NVARCHAR(50)='962E53FD-4593-4638-AB5B-E04859783AFC';
DECLARE @NewCZVipCtgGroupGuid NVARCHAR(50)='D2ECF5E4-B83B-4375-997F-5F587960633A';
DECLARE @NewCZNormalCtgGroupGuid NVARCHAR(50)='591D62FF-D36D-4F77-963D-69BA033ED30D';

DECLARE @sSalesGuid NVARCHAR(50), @sCityId NVARCHAR(50), @sVersionId NVARCHAR(50);
DECLARE @sCityGuid NVARCHAR(50), @sAreaGuidCZ NVARCHAR(50), @sSalesSource NVARCHAR(50);
DECLARE @sSrvGrpGuidCZ NVARCHAR(50), @sSrvGrpNameCZ NVARCHAR(50), @sServiceGuidCZ NVARCHAR(50);
DECLARE @sServiceNameCZ NVARCHAR(50), @sCustGuid NVARCHAR(50), @sCustName NVARCHAR(50);
DECLARE @sCategory NVARCHAR(50), @sIsNewCust NVARCHAR(50), @sPhoneGuid NVARCHAR(50);
DECLARE @sAddressGuid NVARCHAR(50), @sCustAddress NVARCHAR(50), @sCoordGuid NVARCHAR(50);
DECLARE @sCoordName NVARCHAR(50), @sReqDate NVARCHAR(50), @sPaid NVARCHAR(50);
DECLARE @sInvoice NVARCHAR(50), @sCommonComment NVARCHAR(50), @sRequestRemark NVARCHAR(50);
DECLARE @sSalesAttribute NVARCHAR(50), @sAddTime NVARCHAR(50), @sCreateTime NVARCHAR(50);
DECLARE @sAddUser NVARCHAR(50), @sCallPhone NVARCHAR(50), @sCoordAddress NVARCHAR(50);

DECLARE @NewWxCityId NVARCHAR(10)='wx.js';
DECLARE @NewSzCityId NVARCHAR(10)='sz.js';

DECLARE @CZFrtGrpGuid NVARCHAR(50)='0478FA49-17AF-420C-85CC-925EBC5ABF17';

--Clear first
PRINT 'Clear First ...';
DELETE FROM [dbo].[BlackList] WHERE [CityId]=@CityId;
--DELETE FROM [dbo].[Customer] WHERE [CityId]=@CityId;
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
DELETE FROM [dbo].[DistributionCenter] WHERE [DCGuid]=@DCGuidCZ;

INSERT INTO [dbo].[DistributionCenter] VALUES (
	@DCGuidCZ,
	@CityId,
	@CityGuid,
	@AreaGuidCZ,
	'常州市区',
	NULL,
	NULL,
	NULL,
	NULL,
	NULL,
	NULL,
	0,
	GETDATE(),
	'System'
);

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
		FROM [Changzhou_Fandian].[dbo].[BlackList]
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
			FROM [Changzhou_Fandian].[dbo].[CancelReason]
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
			FROM [Changzhou_Fandian].[dbo].[Category] AS fc
				INNER JOIN [Changzhou_Fandian].[dbo].[CategoryStandard] AS tcs
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
			FROM [Changzhou_Fandian].[dbo].[CategoryGroup]
			WHERE [CtgGroupName] NOT IN (
				SELECT [CtgGroupName]
					FROM [dbo].[CategoryGroup]
			)
	;
	DELETE FROM [dbo].[CategoryGroup]
		WHERE [CtgGroupGuid]=@OldCZVIPCtgGroupGuid;
	DELETE FROM [dbo].[CategoryGroup]
		WHERE [CtgGroupGuid]=@OldCZNormalCtgGroupGuid;
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
			FROM [Changzhou_Fandian].[dbo].[CategoryGroupMember] AS cgm
				INNER JOIN [Changzhou_Fandian].[dbo].[Category] AS c
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
			INNER JOIN [Changzhou_Fandian].[dbo].[CategoryGroup] AS ocg
				ON ocg.CtgGroupName=cg.CtgGroupName
			INNER JOIN [Changzhou_Fandian].[dbo].[CategoryGroupMember] AS ocgm
				ON ocgm.[CtgGroupGuid]=ocg.[CtgGroupGuid]
			LEFT JOIN [dbo].[CategoryGroupMember] AS cgm
				ON cgm.[CtgGroupGuid]=cg.[CtgGroupGuid]
			LEFT JOIN [dbo].[Category] AS c
				ON cgm.[CtgGuid]=c.[CtgGuid]
		WHERE cgm.[CtgGuid] IS NULL
	;
	DELETE FROM [dbo].[CategoryGroupMember]
		WHERE [CtgGroupGuid]=@OldCZVIPCtgGroupGuid;
	DELETE FROM [dbo].[CategoryGroupMember]
		WHERE [CtgGroupGuid]=@OldCZNormalCtgGroupGuid;
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
			FROM [Changzhou_Fandian].[dbo].[CategoryStandard]
	;
	--Coordinate	PASS
	--CoordinateUpload	PASS
	--Customer
	PRINT 'Table [dbo].[Customer] ...';
	INSERT INTO [dbo].[Customer] (
		[CustGuid],
		--[CityId],
		--[CityGuid],
		--[AreaGuid],
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
			--@CityId,
			--@CityGuid,
			--@AreaGuidCZ,
			[CustId],
			[CustName],
			[CtgGroupGuid],
			[Company],
			[Mail],
			[Remark],
			[Disabled],
			[AddTime],
			[AddUser]
		FROM [Changzhou_Fandian].[dbo].[Customer]
		WHERE [CustGuid] NOT IN (
			SELECT [CustGuid]
				FROM [dbo].[Customer]
		)
	;
	UPDATE [dbo].[Customer]
		SET [CtgGroupGuid]=@NewCZVipCtgGroupGuid
		WHERE CtgGroupGuid=@OldCZVIPCtgGroupGuid;
	UPDATE [dbo].[Customer]
		SET [CtgGroupGuid]=@NewCZNormalCtgGroupGuid
		WHERE CtgGroupGuid=@OldCZNormalCtgGroupGuid;
	--CustomerAddress
	PRINT 'Table [dbo].[CustomerAddress] ...';
	INSERT INTO [dbo].[CustomerAddress] (
		[AddressGuid],
		[CityId],
		[CityGuid],
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
			@CityGuid,
			[CustGuid],
			[CustAddress],
			[CoordGuid],
			[CoordName],
			[CoordValue],
			[Remark],
			[Disabled],
			[AddTime],
			[AddUser]
		FROM [Changzhou_Fandian].[dbo].[CustomerAddress]
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
	FROM [Changzhou_Fandian].[dbo].[CustomerExpedite]
	WHERE [XpdGuid] NOT IN (
		SELECT [XpdGuid]
			FROM [dbo].[CustomerExpedite]
	)
	;
	--CustomerPhone
	PRINT 'Table [dbo].[CustomerPhone] ...';
	INSERT INTO [dbo].[CustomerPhone] (
		[PhoneGuid],
		--[CityId],
		[CityGuid],
		[CustGuid],
		[PhoneNumber],
		[PhoneType],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [PhoneGuid],
	--	@CityId,
		@CityGuid,
		[CustGuid],
		[PhoneNumber],
		[PhoneType],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	FROM [Changzhou_Fandian].[dbo].[CustomerPhone]
	WHERE [PhoneGuid] NOT IN (
		SELECT [PhoneGuid]
			FROM [dbo].[CustomerPhone]
	) AND [PhoneType]=1
	;
	INSERT INTO [dbo].[CustomerPhone] (
		[PhoneGuid],
		--[CityId],
		[CityGuid],
		[CustGuid],
		[PhoneNumber],
		[PhoneType],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [PhoneGuid],
	--	@CityId,
		@CityGuid,
		[CustGuid],
		CONCAT('0519', [PhoneNumber]),
		[PhoneType],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	FROM [Changzhou_Fandian].[dbo].[CustomerPhone]
	WHERE [PhoneGuid] NOT IN (
		SELECT [PhoneGuid]
			FROM [dbo].[CustomerPhone]
	) AND [PhoneType]=0 AND [PhoneNumber] NOT LIKE '0519%'
	;	
	INSERT INTO [dbo].[CustomerPhone] (
		[PhoneGuid],
		--[CityId],
		[CityGuid],
		[CustGuid],
		[PhoneNumber],
		[PhoneType],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	)
	SELECT [PhoneGuid],
	--	@CityId,
		@CityGuid,
		[CustGuid],
		[PhoneNumber],
		[PhoneType],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
	FROM [Changzhou_Fandian].[dbo].[CustomerPhone]
	WHERE [PhoneGuid] NOT IN (
		SELECT [PhoneGuid]
			FROM [dbo].[CustomerPhone]
	) AND [PhoneType]=0 AND [PhoneNumber] LIKE '0519%'
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
			FROM [Changzhou_Fandian].[dbo].[CustOrder]
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
		@DCGuidCZ,
		[DlvManId],
		[DlvManName],
		@StaffType,
		[WorkPhone],
		[DlvManPassword],
		[InputCode],
		[Disabled],
		[AddTime],
		[AddUser],
		[LastHeartBeat],
		[LastLongitude],
		[LastLatitude]
		FROM [Changzhou_Fandian].[dbo].[Deliveryman]
	;
	
	--DeliverySchedule	PASS
	
	--DeliveryScheduleTime	PASS
	
	--DeliveryShift	PASS
	
	--DeliveryShiftTime	PASS
	
	--DistributionCenter PASS

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
		FROM [Changzhou_Fandian].[dbo].[DivisionGroup]
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
			FROM [Changzhou_Fandian].[dbo].[DivisionGroupMember]
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
		FROM [Changzhou_Fandian].[dbo].[DivisionGroupScheme]
	;
	
	--Enum	PASS
	
	--FreightGroup PASS
	
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
			FROM [Changzhou_Fandian].[dbo].[IssueLastVersion]
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
		FROM [Changzhou_Fandian].[dbo].[ItemUnit]
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
		@SrvCmbGuidCZ,
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
		FROM [Changzhou_Fandian].[dbo].[Item] AS i
			INNER JOIN [Changzhou_Fandian].[dbo].[CategoryGroup] AS cg
				ON cg.[CtgGroupGuid]=i.[CtgGroupGuid]
			LEFT JOIN [dbo].[CategoryGroup] AS ncg
				ON ncg.[CtgGroupName]=cg.[CtgGroupName]
			INNER JOIN [Changzhou_Fandian].[dbo].[Vendor] AS v
				ON i.[VendorGuid]=v.[VendorGuid]
			INNER JOIN [Changzhou_Fandian].[dbo].[ItemUnit] AS iu
				ON iu.[UnitGuid]=i.[UnitGuid]
			INNER JOIN (
					SELECT u.[UnitGuid], u.[UnitName]
						FROM [dbo].[ItemUnit] AS u
							INNER JOIN [Changzhou_Fandian].[dbo].[ItemUnit] AS u2
								ON u2.[UnitName]=u.[UnitName]
				) AS iu2
				ON iu2.[UnitName]=iu.[UnitName]
	WHERE i.[ItemGuid] NOT IN (
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
			FROM [Changzhou_Fandian].[dbo].[ItemProperty]
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
		FROM [Changzhou_Fandian].[dbo].[Order] AS o
			INNER JOIN [Changzhou_Fandian].[dbo].[OrderVersion] AS ov
				ON ov.OrderGuid=o.OrderGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[OrderLastVersion] AS olv
				ON olv.OrdVerGuid=ov.OrdVerGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[OrderItemVersion] AS oiv
				ON oiv.OIVGuid=ov.OIVGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[SalesVersion] AS sv
				ON sv.SalesVerGuid=ov.SalesVerGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[Sales] AS s
				ON s.SalesGuid=o.SalesGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[FreightVersion] AS fv
				ON fv.FrtVerGuid=ov.FrtVerGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[Purchase] AS p
				ON p.PurchGuid=o.PurchGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[Vendor] AS v
				ON v.VendorGuid=p.VendorGuid
	;
	DELETE FROM [dbo].[OrderVersion] FROM [dbo].[OrderVersion] AS ov
		INNER JOIN [dbo].[Order] AS o
			ON ov.OrderGuid=o.OrderGuid
	WHERE ov.[VersionId]=o.[VersionId];
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
			FROM [Changzhou_Fandian].[dbo].[OrderCancel] AS oc
				INNER JOIN [Changzhou_Fandian].[dbo].[CancelReason] AS cr
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
			FROM [Changzhou_Fandian].[dbo].[OrderCancel] AS oc
				INNER JOIN [Changzhou_Fandian].[dbo].[CancelReason] AS cr
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
		FROM [Changzhou_Fandian].[dbo].[Order] AS o
			INNER JOIN [Changzhou_Fandian].[dbo].[OrderVersion] AS ov
				ON ov.OrderGuid=o.OrderGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[OrderLastVersion] AS olv
				ON olv.OrdVerGuid=ov.OrdVerGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[FreightVersion] AS fv
				ON fv.FrtVerGuid=ov.FrtVerGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[Purchase] AS p
				ON p.PurchGuid=o.PurchGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[OrderItemVersion] AS oiv
				ON oiv.OIVGuid=ov.OIVGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[OIV_Item] AS oivi
				ON oivi.OIVGuid=oiv.OIVGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[OrderItem] AS oi
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
	FROM [Changzhou_Fandian].[dbo].[OrderVersion] AS ov
		INNER JOIN [Changzhou_Fandian].[dbo].[OIV_Item] AS oivi
			ON oivi.OIVGuid=ov.OIVGuid
		INNER JOIN [Changzhou_Fandian].[dbo].[OrderItem] AS oi
			ON oi.[OrdItemGuid]=oivi.OrdItemGuid
		INNER JOIN [Changzhou_Fandian].[dbo].[Order] AS o
			ON o.[OrderGuid]=ov.[OrderGuid]
	WHERE ov.[OrdVerGuid] NOT IN (
		SELECT ov.OrdVerGuid
			FROM [Changzhou_Fandian].[dbo].[Order] AS o
				INNER JOIN [Changzhou_Fandian].[dbo].[OrderVersion] AS ov
					ON ov.[OrderGuid]=o.[OrderGuid]
				INNER JOIN [Changzhou_Fandian].[dbo].[OrderLastVersion] AS olv
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
		FROM [Changzhou_Fandian].[dbo].[OrderLoss]
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
		FROM [Changzhou_Fandian].[dbo].[OrderOnlinePay]
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
		FROM [Changzhou_Fandian].[dbo].[OrderPayment]
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
		FROM [Changzhou_Fandian].[dbo].[OrderStatusLog]
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
		FROM [Changzhou_Fandian].[dbo].[OIV_Item]
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
		FROM [Changzhou_Fandian].[dbo].[OrderVersion] AS ov
			INNER JOIN [Changzhou_Fandian].[dbo].[Order] AS o
				ON o.OrderGuid=ov.OrderGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[OrderItemVersion] AS oiv
				ON oiv.OIVGuid=ov.OIVGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[Purchase] AS p
				ON p.PurchGuid=o.PurchGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[FreightVersion] AS fv
				ON fv.FrtVerGuid=ov.FrtVerGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[SalesVersion] AS sv
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
	
	--Region PASS

	--RegionLevel	PASS
	
	--Rights	PASS
	
	--Sales
	PRINT 'Table [dbo].[Sales] ...';
	DECLARE SZSalesCursor CURSOR
		FOR SELECT DISTINCT o.SalesGuid, 
			@CityId,
			sv.VersionId,
			@CityGuid,
			@AreaGuidCZ,
			s.SalesSource,
			@SrvGrpGuidCZ,
			--sv.SrvGrpGuid,
			@SrvGrpNameCZ,
			--sv.SrvGrpName,
			@ServiceGuidCZ,
			--sv.ServiceGuid,
			@ServiceNameCZ,
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
		FROM [Changzhou_Fandian].[dbo].[Order] AS o
			INNER JOIN [Changzhou_Fandian].[dbo].[OrderVersion] AS ov
				ON ov.OrderGuid=o.OrderGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[OrderLastVersion] AS olv
				ON olv.OrdVerGuid=ov.OrdVerGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[SalesVersion] AS sv
				ON sv.SalesVerGuid=ov.SalesVerGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[Sales] AS s
				ON s.SalesGuid=o.SalesGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[Purchase] AS p
				ON p.PurchGuid=o.PurchGuid
			INNER JOIN [Changzhou_Fandian].[dbo].[Vendor] AS v
				ON v.VendorGuid=p.VendorGuid
	OPEN SZSalesCursor
	FETCH NEXT FROM SZSalesCursor
		INTO 
		@sSalesGuid, @sCityId, @sVersionId, @sCityGuid, @sAreaGuidCZ, @sSalesSource, @sSrvGrpGuidCZ,
		@sSrvGrpNameCZ, @sServiceGuidCZ, @sServiceNameCZ, @sCustGuid, @sCustName, @sCategory, 
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
			@AreaGuidCZ,
			s.SalesSource,
			@SrvGrpGuidCZ,
			--sv.SrvGrpGuid,
			@SrvGrpNameCZ,
			--sv.SrvGrpName,
			@ServiceGuidCZ,
			--sv.ServiceGuid,
			@ServiceNameCZ,
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
			FROM [Changzhou_Fandian].[dbo].[Order] AS o
				INNER JOIN [Changzhou_Fandian].[dbo].[OrderVersion] AS ov
					ON ov.OrderGuid=o.OrderGuid
				INNER JOIN [Changzhou_Fandian].[dbo].[OrderLastVersion] AS olv
					ON olv.OrdVerGuid=ov.OrdVerGuid
				INNER JOIN [Changzhou_Fandian].[dbo].[SalesVersion] AS sv
					ON sv.SalesVerGuid=ov.SalesVerGuid
				INNER JOIN [Changzhou_Fandian].[dbo].[Sales] AS s
					ON s.SalesGuid=o.SalesGuid
				INNER JOIN [Changzhou_Fandian].[dbo].[Purchase] AS p
					ON p.PurchGuid=o.PurchGuid
				INNER JOIN [Changzhou_Fandian].[dbo].[Vendor] AS v
					ON v.VendorGuid=p.VendorGuid
		WHERE o.SalesGuid=@sSalesGuid AND o.SalesGuid NOT IN (
				SELECT TOP 1 [SalesGuid]
					FROM [dbo].[Sales]
			)
			PRINT 'Cursor Sales '+@sSalesGuid
			FETCH NEXT FROM SZSalesCursor
				INTO 
				@sSalesGuid, @sCityId, @sVersionId, @sCityGuid, @sAreaGuidCZ, @sSalesSource, @sSrvGrpGuidCZ,
				@sSrvGrpNameCZ, @sServiceGuidCZ, @sServiceNameCZ, @sCustGuid, @sCustName, @sCategory, 
				@sIsNewCust, @sPhoneGuid, @sCallPhone, @sAddressGuid, @sCustAddress, 
				@sCoordGuid, @sCoordName, @sReqDate, @sPaid, @sInvoice, @sCommonComment, @sRequestRemark,
				@sSalesAttribute, @sAddTime, @sCreateTime, @sAddUser		
		
	END
	CLOSE SZSalesCursor
	DEALLOCATE SZSalesCursor			
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
			FROM [Changzhou_Fandian].[dbo].[SalesAttribute]
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
			@AreaGuidCZ,
			s.SalesSource,
			@SrvGrpGuidCZ,
			@SrvGrpNameCZ,
			@ServiceGuidCZ,
			@ServiceNameCZ,
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
			FROM [Changzhou_Fandian].[dbo].[SalesVersion] AS sv
				INNER JOIN [Changzhou_Fandian].[dbo].[Sales] AS s
					ON s.SalesGuid=sv.SalesGuid
		WHERE sv.SalesGuid IN (
			SELECT DISTINCT
						sv.SalesVerGuid
						FROM [Changzhou_Fandian].[dbo].[SalesVersion] AS sv
							INNER JOIN [Changzhou_Fandian].[dbo].[Sales] AS s
								ON s.SalesGuid=sv.SalesGuid
							INNER JOIN [Changzhou_Fandian].[dbo].[Order] AS o
								ON o.SalesGuid=s.SalesGuid
							INNER JOIN [Changzhou_Fandian].[dbo].[OrderVersion] AS ov
								ON ov.SalesVerGuid=sv.SalesVerGuid AND ov.OrderGuid=o.OrderGuid
							INNER JOIN [Changzhou_Fandian].[dbo].[Purchase] AS p
								ON p.PurchGuid=o.PurchGuid
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
			FROM [Changzhou_Fandian].[dbo].[SortGroup]
	;
	--User ????
	PRINT 'Table [dbo].[User] ...';
	INSERT INTO [dbo].[User] (
		[UserGuid],
		[UserId],
		[UserName],
		[Password],
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
			[IsAdmin],
			[PermGroupGuid],
			[Disabled],
			[AddTime],
			[AddUser]
			FROM [Changzhou_Fandian].[dbo].[User]
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
			@AreaGuidCZ,
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
			FROM [Changzhou_Fandian].[dbo].[Vendor]
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
			FROM [Changzhou_Fandian].[dbo].[VendorAddress]
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
			FROM [Changzhou_Fandian].[dbo].[VendorContactMethod]
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
			FROM [Changzhou_Fandian].[dbo].[VendorContactPerson]
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
			FROM [Changzhou_Fandian].[dbo].[VendorCorporate]
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
			FROM [Changzhou_Fandian].[dbo].[VendorServiceTime]
	;
	--W_Addressbook
	PRINT 'Table [dbo].[W_Addressbook] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_Addressbook]
	;
	--W_Article
	PRINT 'Table [dbo].[W_Article] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_Article]
	;
	--W_ArticleCategory
	PRINT 'Table [dbo].[W_ArticleCategory] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_ArticleCategory]
	;
	SET IDENTITY_INSERT [dbo].[W_ArticleCategory] OFF;
	--W_Attachment
	PRINT 'Table [dbo].[W_Attachment] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_Attachment]
	;
	--W_AttachmentData
	PRINT 'Table [dbo].[W_AttachmentData] ...';
	INSERT INTO [dbo].[W_AttachmentData] (
		[FileId],
		[Data]
	)
	SELECT [FileId],
			[Data]
			FROM [Changzhou_Fandian].[dbo].[W_AttachmentData]
	;
	--W_BaiduPlaceLog
	PRINT 'Table [dbo].[W_BaiduPlaceLog] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_BaiduPlaceLog]
	;
	--W_CoordForBaidu
	PRINT 'Table [dbo].[W_CoordForBaidu] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_CoordForBaidu]
	;
	--W_FavoritedItems
	PRINT 'Table [dbo].[W_FavoritedItems] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_FavoritedItems]
	;
	--W_FavoritedVendors
	PRINT 'Table [dbo].[W_FavoritedVendors] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_FavoritedVendors]
	;
	--W_Feedback
	PRINT 'Table [dbo].[W_Feedback] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_Feedback]
	;
	--W_ItemExtend
	PRINT 'Table [dbo].[W_ItemExtend] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_ItemExtend]
	;
	--W_OrderAnalysis	PASS
	
	--W_OrderAnnounce
	PRINT 'Table [dbo].[W_OrderAnnounce] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_OrderAnnounce]
	;
	--W_OrderHash
	PRINT 'Table [dbo].[W_OrderHash] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_OrderHash]
	;
	--W_ResetPasswordHash
	PRINT 'Table [dbo].[W_ResetPasswordHash] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_ResetPasswordHash]
	;
	--W_SearchLogs
	PRINT 'Table [dbo].[W_SearchLogs] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_SearchLogs]
	;
	--W_SystemVars
	PRINT 'Table [dbo].[W_SystemVars] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_SystemVars]
	;
	--W_TencentConnectToken
	PRINT 'Table [dbo].[W_TencentConnectToken] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_TencentConnectToken]
	;
	--W_Users
	PRINT 'Table [dbo].[W_Users] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_Users]
	;
	--W_VendorExtend
	PRINT 'Table [dbo].[W_VendorExtend] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_VendorExtend]
	;
	--W_WeiboOauthToken
	PRINT 'Table [dbo].[W_WeiboOauthToken] ...';
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
			FROM [Changzhou_Fandian].[dbo].[W_WeiboOauthToken]
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
		FROM [Changzhou_Fandian].[dbo].[Category] AS c
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

--Fix Order/OrderVersion
UPDATE [dbo].[Order] SET [InfoChanged]=0 WHERE [VersionId]=0;
UPDATE [dbo].[OrderVersion] SET [InfoChanged]=0 WHERE [VersionId]=0;

--FIX 商圈
PRINT 'Fix 商圈 ...';
DECLARE @VendorGuid NVARCHAR(50), @BizArea NVARCHAR(50);
DECLARE baCursor CURSOR
	FOR
		SELECT v.VendorGuid, (r.RegionName+','+c.CtgName) AS [BizArea]
			FROM [Changzhou_Fandian].[dbo].[Vendor] AS v
				INNER JOIN [Changzhou_Fandian].[dbo].[Region] AS r
					ON r.RegionGuid=v.RegionGuid
				INNER JOIN [Changzhou_Fandian].[dbo].[CategoryGroup] AS cg
					ON cg.CtgGroupGuid=v.CtgGroupGuid
				INNER JOIN [Changzhou_Fandian].[dbo].[CategoryGroupMember] AS cgm
					ON cgm.CtgGroupGuid=cg.CtgGroupGuid
				INNER JOIN [Changzhou_Fandian].[dbo].[Category] AS c
					ON c.CtgGuid=cgm.CtgGuid
				INNER JOIN [Changzhou_Fandian].[dbo].[CategoryStandard] AS cs
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
UPDATE [dbo].[Service] SET [FrtGrpGuid]=@CZFrtGrpGuid WHERE [CityId]=@CityId;
	
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
UPDATE [dbo].[W_Article] SET [RegionGuid]=@NewCZRegionGuid WHERE [RegionGuid]=@OldCZRegionGuid;

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
UPDATE [dbo].[W_Feedback] SET [RegionGuid]=@NewCZRegionGuid WHERE [RegionGuid]=@OldCZRegionGuid;

--UPDATE [dbo].[W_ItemExtend] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_ItemExtend] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_OrderAnnounce] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_OrderAnnounce] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;
UPDATE [dbo].[W_OrderAnnounce] SET [RegionGuid]=@NewCZRegionGuid WHERE [RegionGuid]=@OldCZRegionGuid;

--UPDATE [dbo].[W_OrderHash] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_OrderHash] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_ResetPasswordHash] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_ResetPasswordHash] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_SearchLogs] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_SearchLogs] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_SystemVars] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_SystemVars] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;
UPDATE [dbo].[W_SystemVars] SET [RegionGuid]=@NewCZRegionGuid WHERE [RegionGuid]=@OldCZRegionGuid;

--UPDATE [dbo].[W_TencentConnectToken] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_TencentConnectToken] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_Users] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_Users] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_VendorExtend] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_VendorExtend] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

--UPDATE [dbo].[W_WeiboOauthToken] SET [CityId]=@NewWxCityId WHERE [CityId]=@WxCityId;
--UPDATE [dbo].[W_WeiboOauthToken] SET [CityId]=@NewSzCityId WHERE [CityId]=@CityId;

UPDATE [dbo].[Vendor] SET [InputCode]=[dbo].[fn_GetPhonetic]([VendorName]) WHERE [CityId]='cz';
UPDATE [dbo].[Item] SET [InputCode]=[dbo].[fn_GetPhonetic]([ItemName]) WHERE [CityId]='cz';
UPDATE [dbo].[Coordinate] SET [InputCode]=[dbo].[fn_GetPhonetic]([CoordName]) WHERE [CityId]='cz';

--CLEAR SOME DATA
PRINT 'Clear some data ...';

PRINT ''
PRINT ''
PRINT '=========================================================='
PRINT 'DONE'
PRINT '=========================================================='
PRINT ''
PRINT ''

