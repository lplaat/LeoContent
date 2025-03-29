import {Sequelize, DataTypes} from 'sequelize';
import dotenv from 'dotenv';

dotenv.config();

export const sequelize = new Sequelize(process.env.MYSQL_DATABASE, process.env.MYSQL_USER, process.env.MYSQL_PASSWORD, {
    host: process.env.MYSQL_HOST,
    port: process.env.MYSQL_PORT,
    dialect: 'mysql',
});

// Defining models
export let Stream;
export let StreamConfig;
export let Media;

export async function databaseInit() {
    await sequelize.authenticate();

    Stream = sequelize.define('Stream', {
        id: {
            type: DataTypes.INTEGER,
            allowNull: false,
            primaryKey: true,
            autoIncrement: true,
        },
        code: {
            type: DataTypes.TEXT,
            allowNull: false,
        },
        user_id: {
            type: DataTypes.INTEGER,
            allowNull: false,
        },
        media_id: {
            type: DataTypes.INTEGER,
            allowNull: false,
        },
        alive: {
            type: DataTypes.BOOLEAN,
            allowNull: false,
            defaultValue: true,
        },
        created_at: {
            type: DataTypes.DATE,
            allowNull: false,
        },
        updated_at: {
            type: DataTypes.DATE,
            allowNull: false,
        },
    }, {
        tableName: 'stream',
        timestamps: false,
        underscored: true,
    });

    StreamConfig = sequelize.define('StreamConfig', {
        id: {
            type: DataTypes.INTEGER,
            primaryKey: true,
            allowNull: false,
            autoIncrement: true
        },
        name: {
            type: DataTypes.TEXT,
            allowNull: false
        },
        value: {
            type: DataTypes.TEXT,
            allowNull: false
        }
    }, {
        tableName: 'stream_config',
        timestamps: false,
        underscored: true
    });

    Media = sequelize.define('Media', {
        id: {
            type: DataTypes.INTEGER,
            primaryKey: true,
            autoIncrement: true,
            allowNull: false,
        },
        path: {
            type: DataTypes.TEXT,
            allowNull: false,
        },
        duration: {
            type: DataTypes.INTEGER,
            allowNull: false,
        },
        quality: {
            type: DataTypes.TEXT,
            allowNull: false,
        },
        media_directory_id: {
            type: DataTypes.INTEGER,
            allowNull: true,
            defaultValue: null,
        },
        content_id: {
            type: DataTypes.INTEGER,
            allowNull: true,
            defaultValue: null,
        },
    }, {
        tableName: 'media',
        timestamps: false,
        underscored: true,
    });

    Stream.belongsTo(Media, {
        foreignKey: 'media_id',
        targetKey: 'id',
        as: 'Media',
    });
}