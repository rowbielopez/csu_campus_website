**CSU CMS Platform - Multi-Campus Content Management System**

# Campus Configuration Files

This directory contains campus-specific configuration files for each CSU campus. Each file defines the unique settings for that campus while sharing the same codebase.

## Campus List

| Campus ID | Campus Code | Domain | Configuration File |
|-----------|-------------|--------|-------------------|
| 1 | andrews | andrews.csu.edu.ph | andrews.php |
| 2 | aparri | aparri.csu.edu.ph | aparri.php |
| 3 | carig | carig.csu.edu.ph | carig.php |
| 4 | gonzaga | gonzaga.csu.edu.ph | gonzaga.php |
| 5 | lallo | lallo.csu.edu.ph | lallo.php |
| 6 | lasam | lasam.csu.edu.ph | lasam.php |
| 7 | piat | piat.csu.edu.ph | piat.php |
| 8 | sanchezmira | sanchezmira.csu.edu.ph | sanchezmira.php |
| 9 | solana | solana.csu.edu.ph | solana.php |

## Configuration Structure

Each campus configuration file contains:

- **Campus Identity**: ID, name, code, domain
- **Contact Information**: Address, phone, email
- **Visual Settings**: Colors, logo paths, favicon
- **Feature Flags**: Enable/disable specific features
- **File Paths**: Upload, cache, and log directories
- **Localization**: Timezone, language settings

## Deployment

During deployment, copy the appropriate configuration file to `config/config.php` in each campus directory:

```bash
# For Andrews Campus
cp config/campus-configs/andrews.php /var/www/andrews/config/config.php

# For Aparri Campus  
cp config/campus-configs/aparri.php /var/www/aparri/config/config.php

# And so on for each campus...
```

## Customization

To customize a specific campus:

1. Edit the appropriate configuration file
2. Redeploy to that campus directory
3. Clear cache if enabled

## Security Note

Each campus should only have access to its own configuration file. The master repository should contain all configs for deployment purposes only.
