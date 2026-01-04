<!DOCTYPE html>
<html>
<head>
    <title>Basic Test</title>
</head>
<body>
    <h1>Basic Test Page</h1>
    <p id="test-output">JavaScript not loaded</p>
    
    <script>
        document.getElementById('test-output').textContent = 'JavaScript is working!';
        console.log('Basic JavaScript test successful');
    </script>
</body>
</html>