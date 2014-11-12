USE [wxfd_allcity];

DROP INDEX IX_Vendor_VendorId ON [dbo].[Vendor];

--W_Addressbook
ALTER TABLE [dbo].[W_Addressbook] ADD CityId NVARCHAR(20);
--W_Article
ALTER TABLE [dbo].[W_Article] ADD CityId NVARCHAR(20) ;
--W_ArticleCategory
ALTER TABLE [dbo].[W_ArticleCategory] ADD CityId NVARCHAR(20) ;
--W_Attachment
ALTER TABLE [dbo].[W_Attachment] ADD CityId NVARCHAR(20) ;
--W_BaiduPlaceLog
ALTER TABLE [dbo].[W_BaiduPlaceLog] ADD CityId NVARCHAR(20) ;
--W_CoordForBaidu
ALTER TABLE [dbo].[W_CoordForBaidu] ADD CityId NVARCHAR(20) ;
--W_FavoritedItems
ALTER TABLE [dbo].[W_FavoritedItems] ADD CityId NVARCHAR(20) ;
--W_FavoritedVendors
ALTER TABLE [dbo].[W_FavoritedVendors] ADD CityId NVARCHAR(20) ;
--W_Feedback
ALTER TABLE [dbo].[W_Feedback] ADD CityId NVARCHAR(20) ;
--W_ItemExtend
ALTER TABLE [dbo].[W_ItemExtend] ADD CityId NVARCHAR(20) ;
ALTER TABLE [dbo].[W_ItemExtend] ADD Persisted NVARCHAR(MAX);
--W_OrderAnnounce
ALTER TABLE [dbo].[W_OrderAnnounce] ADD CityId NVARCHAR(20) ;
--W_OrderHash
--ALTER TABLE [dbo].[W_OrderHash] ADD CityId NVARCHAR(20) ;
--W_ResetPasswordHash
ALTER TABLE [dbo].[W_ResetPasswordHash] ADD CityId NVARCHAR(20) ;
--W_SearchLogs
ALTER TABLE [dbo].[W_SearchLogs] ADD CityId NVARCHAR(20) ;
--W_SystemVars
ALTER TABLE [dbo].[W_SystemVars] ADD CityId NVARCHAR(20) ;
--W_TencentConnectToken
ALTER TABLE [dbo].[W_TencentConnectToken] ADD CityId NVARCHAR(20) ;
--W_Users
ALTER TABLE [dbo].[W_Users] ADD CityId NVARCHAR(20) ;
--W_VendorExtend
ALTER TABLE [dbo].[W_VendorExtend] ADD CityId NVARCHAR(20) ;
ALTER TABLE [dbo].[W_VendorExtend] ADD BizArea NVARCHAR(50);
--W_WeiboOauthToken
ALTER TABLE [dbo].[W_WeiboOauthToken] ADD CityId NVARCHAR(20) ;
