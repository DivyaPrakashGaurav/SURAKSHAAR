-- Enable RLS on the relevant tables if not already enabled
ALTER TABLE workers ENABLE ROW LEVEL SECURITY;
ALTER TABLE training_attempts ENABLE ROW LEVEL SECURITY;
ALTER TABLE certificates ENABLE ROW LEVEL SECURITY;

-- 1. Allow the Flutter app (using anon key) to insert a new worker
CREATE POLICY "Allow anon insert for workers"
ON workers
FOR INSERT
TO anon
WITH CHECK (
    worker_id IS NOT NULL 
    AND name IS NOT NULL
);

-- Note: If Flutter performs an .insert().select() to get the generated internal ID (workers.id), 
-- it needs a SELECT policy to read the row it just inserted.
-- We restrict SELECT to the anon role only if it's genuinely needed by the app. 
-- Assuming Flutter needs to map workers.id to related tables, we enable SELECT.
CREATE POLICY "Allow anon select for workers"
ON workers
FOR SELECT
TO anon
USING (true);

-- 2. Allow the Flutter app to insert training attempts
-- Assuming Flutter maps the returned workers.id into training_attempts.worker_id
CREATE POLICY "Allow anon insert for training_attempts"
ON training_attempts
FOR INSERT
TO anon
WITH CHECK (
    worker_id IS NOT NULL 
    AND module_id IS NOT NULL
);

-- 3. Allow the Flutter app to insert certificates
CREATE POLICY "Allow anon insert for certificates"
ON certificates
FOR INSERT
TO anon
WITH CHECK (
    certificate_id IS NOT NULL 
    AND worker_id IS NOT NULL 
    AND module_id IS NOT NULL
);
