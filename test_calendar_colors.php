<?php
// test_calendar_colors.php
echo "<h2>Calendar Color Scheme Test</h2>";

echo "<h3>New Color Scheme:</h3>";
echo "<div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>";
echo "<h4>Calendar Event Colors:</h4>";
echo "<ul>";
echo "<li><span style='background-color: #4CAF50; color: white; padding: 2px 6px; border-radius: 3px;'>School Year Events</span> - Green (Add School Year)</li>";
echo "<li><span style='background-color: #A99F30; color: white; padding: 2px 6px; border-radius: 3px; font-weight: bold;'>Term Events</span> - Yellow (Add Term)</li>";
echo "<li><span style='background-color: #2196F3; color: white; padding: 2px 6px; border-radius: 3px;'>Holiday Events</span> - Blue (Add Holiday)</li>";
echo "<li><span style='background-color: #f44336; color: white; padding: 2px 6px; border-radius: 3px;'>Maintenance Events</span> - Red (Schedule Maintenance)</li>";
echo "</ul>";
echo "<p><strong>Note:</strong> Term events now use yellow color to match the 'Add Term' management option.</p>";
echo "</div>";

echo "<h3>Management Options Color Mapping:</h3>";
echo "<div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>";
echo "<ul>";
echo "<li><span style='background-color: #4CAF50; color: white; padding: 2px 6px; border-radius: 3px;'>Add School Year</span> → <span style='background-color: #4CAF50; color: white; padding: 2px 6px; border-radius: 3px;'>Green Calendar Events</span></li>";
echo "<li><span style='background-color: #A99F30; color: white; padding: 2px 6px; border-radius: 3px; font-weight: bold;'>Add Term</span> → <span style='background-color: #A99F30; color: white; padding: 2px 6px; border-radius: 3px; font-weight: bold;'>Yellow Calendar Events</span></li>";
echo "<li><span style='background-color: #2196F3; color: white; padding: 2px 6px; border-radius: 3px;'>Add Holiday</span> → <span style='background-color: #2196F3; color: white; padding: 2px 6px; border-radius: 3px;'>Blue Calendar Events</span></li>";
echo "<li><span style='background-color: #f44336; color: white; padding: 2px 6px; border-radius: 3px;'>Schedule Maintenance</span> → <span style='background-color: #f44336; color: white; padding: 2px 6px; border-radius: 3px;'>Red Calendar Events</span></li>";
echo "</ul>";
echo "</div>";

echo "<h3>Next Steps:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=school-calendar&v=10' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 View Updated Calendar Colors</a></p>";
?>
