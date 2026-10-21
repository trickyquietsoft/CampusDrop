# Change Log - CampusDrop

## 2026-10-07
- **Bug Fix**: My Posts on `my_account.php` showed all university listings instead of user-specific ones.
- **API Improvement**: Moved listing filtering logic from frontend to API layer.
    - Added `action=get_mine` to `listing_api.php` to explicitly fetch the current user's listings.
    - Updated `my_account.php` to call the new `get_mine` endpoint.
