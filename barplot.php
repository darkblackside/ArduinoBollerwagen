<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display csv values</title>
    <script src="https://d3js.org/d3.v7.min.js"></script>
</head>
<body>
    <svg id="barplot"></svg>
	<?php
		$useFunc = ($_GET && $_GET["func"]) ? $_GET["func"] : "1";
	?>

    <script>
        // Set up the SVG dimensions
        const svgWidth = 350;
        const svgHeight = 300;
        const margin = { top: 25, right: 25, bottom: 50, left: 50 };
        const width = svgWidth - margin.left - margin.right;
        const height = svgHeight - margin.top - margin.bottom;

        // Append SVG to the body
        const svg = d3.select("#barplot")
            .attr("width", svgWidth)
            .attr("height", svgHeight);

        // Load the CSV file
        d3.dsv(";", "history.csv").then(data => {
			data = data.map(x => x);
			console.log(data);
            // Filter data where the seventh value is 1
            const filteredData = data.filter(d => d['useFunc'] === '<?php echo $useFunc ?>');

            // Convert the first and second values to numbers
            filteredData.forEach(d => {
                d.value1 = +d['read'];
                d.value2 = +d['set'];
            });

                 // Create scales
            const xScale = d3.scaleLinear()
                .domain(d3.extent(filteredData, d => d.value1))
                .range([margin.left, width + margin.left]);

            const yScale = d3.scaleLinear()
                .domain([0, d3.max(filteredData, d => d.value2)])
                .range([height, margin.top]);

            // Draw the line
            svg.append("path")
                .datum(filteredData)
                .attr("fill", "none")
                .attr("stroke", "steelblue")
                .attr("stroke-width", 2);

            // Draw dots
            svg.selectAll("circle")
                .data(filteredData)
                .enter().append("circle")
                .attr("cx", d => xScale(d.value1))
                .attr("cy", d => yScale(d.value2))
                .attr("r", 4)
                .attr("fill", "steelblue");

            // Create x-axis
            svg.append("g")
                .attr("transform", `translate(0, ${height})`)
                .call(d3.axisBottom(xScale))
                .selectAll("text")
                    .style("fill", "black");

            // Create y-axis
            svg.append("g")
                .attr("transform", `translate(${margin.left}, 0)`)
                .call(d3.axisLeft(yScale))
                .selectAll("text")
                    .style("fill", "black");
        });
    </script>
	<?php 
	header("Refresh:5");
	?>
</body>
</html>