CREATE TABLE llx_modulespectacle_spectacle_category (
    rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
    label varchar(255) NOT NULL
)
ENGINE=InnoDB;

ALTER TABLE llx_modulespectacle_spectacle_category ADD COLUMN default_amount double;