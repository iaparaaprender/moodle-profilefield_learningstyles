# Learning styles #

This is a profilefield component of Moodle that allows people to inquire about their learning style using the Felder-Silverman test and additional variations of it.

View the user docs: [[en](docs/en/users.md)] [[es](docs/es/users.md)]

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/user/profile/field/learningstyles

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

# Configure the component #

To use the component, you need to add the field to the user's profile, in:
```
Site administration / Users / User profile fields
```

![User profile field](docs/en/setting_newfield.png)

A **“Learning Styles”** type field is added and configured taking care that the user can view it.

![Setting form](docs/en/setting_form.png)

**Note:** the field can be added several times but the results will always be associated directly to the user, not for each instance of the field, so it does not make sense to do it more than once.


## License ##

2024 David Herney - cirano

Component developed within the framework of the SGRAD project.

Project financed by the General Royalties System of Colombia and executed by
the Universidad de Antioquia. BPIN-2021000100186.

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
