# 🚀 CampusDrop Project TODO

## 👤 User Account & Profile
- [ ] Create `my_account.php`: A central hub for students to manage their presence.
- [ ] Create `my_trades.php`: Integration with `trade_api.php?action=get_trades` to view active and completed transactions.
- [ ] Implement Profile Editing: Allow users to update `phone_number` and personal details.
- [ ] Implement User Verification: Logic to move `account_status` from `pending_verification` to `active`.

## 🤝 Trade & Marketplace Logic
- [ ] Trade Status UI: Add buttons to `my_trades.php` to mark trades as 'accepted', 'completed', or 'cancelled' (calling `trade_api.php?action=update_status`).
- [ ] Search Refinement: Implement a more robust filtering system (e.g., filter by Condition or Course Code).
- [ ] Listing Management: A "My Listings" section to edit or delete existing posts.

## 🛡️ Administration & Security
- [ ] Admin Auth Guard: Implement session check on `admin_dashboard.php` to ensure only users in the `admins` table can access it.
- [ ] Moderation Actions: Connect "Remove" and "Dismiss" buttons in the Admin Dashboard to actual API endpoints.
- [ ] Audit Log Viewer: Add a page for super-admins to view the `audit_logs` table.
- [ ] Password Reset: Implement a "Forgot Password" workflow.

## 🎨 UI/UX Enhancements
- [ ] Notification System: Add toast notifications for successful actions (e.g., "Listing Created!").
- [ ] Loading States: Add spinners/skeletons for API calls to improve perceived performance.
- [ ] Responsive Design Audit: Ensure the eBay-style product grid works perfectly on mobile devices.

## 🛠️ Technical Debt & Testing
- [ ] Migrate `schema_v2.sql` to `schema_v3.sql` in the actual live database.
- [ ] Comprehensive Testing: End-to-end test of the Trade $	o$ Message $	o$ Complete flow.
