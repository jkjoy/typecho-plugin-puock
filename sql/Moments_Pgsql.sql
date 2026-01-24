CREATE TABLE "typecho_moments" (
  "id" serial PRIMARY KEY,
  "rowStatus" varchar(16) NOT NULL DEFAULT 'NORMAL',
  "creatorId" integer NOT NULL DEFAULT 0,
  "createdTs" integer NOT NULL DEFAULT 0,
  "updatedTs" integer NOT NULL DEFAULT 0,
  "displayTs" integer NOT NULL DEFAULT 0,
  "content" text NOT NULL,
  "visibility" varchar(16) NOT NULL DEFAULT 'PUBLIC',
  "pinned" integer NOT NULL DEFAULT 0,
  "parent" integer DEFAULT NULL
);
