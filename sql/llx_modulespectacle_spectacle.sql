-- Copyright (C) ---Put here your own copyright and developer email---
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see http://www.gnu.org/licenses/.


CREATE TABLE llx_modulespectacle_spectacle(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) NOT NULL, 
	label varchar(255), 
	amount double(24,8) DEFAULT NULL, 
	date_creation datetime NOT NULL, 
	tms timestamp, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	import_key varchar(14), 
	date datetime
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;

ALTER TABLE llx_modulespectacle_spectacle ADD COLUMN category integer;
ALTER TABLE llx_modulespectacle_spectacle ADD FOREIGN KEY fk_llx_modulespectacle_spectacle_category_rowid(category) REFERENCES llx_modulespectacle_spectacle_category(rowid) ON DELETE SET NULL;
ALTER TABLE llx_modulespectacle_spectacle ADD COLUMN fk_product integer;
ALTER TABLE llx_modulespectacle_spectacle ADD FOREIGN KEY fk_llx_modulespectacle_spectacle_product_rowid(fk_product) REFERENCES llx_product(rowid) ON DELETE SET NULL;