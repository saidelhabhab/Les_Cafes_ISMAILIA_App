<template>
  <div>
    <h2>{{ $t('analytics.title') }}</h2>

    <label for="year-select" class="form-label d-flex align-items-center">
      <i class="fa fa-calendar me-2"></i> <!-- Calendar icon -->
      {{ $t('analytics.selectYear') }}
    </label>
    <select
      id="year-select"
      v-model="selectedYear"
      @change="fetchMonthlyData"
      class="form-select mb-3"
    >
      <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
    </select>

    <h3>{{ $t('analytics.totalAmount', { year: selectedYear, amount: totalAmount }) }}</h3> <!-- Display total amount -->

    <h3>{{ $t('analytics.salesBarChart') }}</h3>
    <apexchart
      type="bar"
      height="350"
      :options="barChartOptions"
      :series="barSeries"
    />

    <h3>{{ $t('analytics.salesLineChart') }}</h3>
    <apexchart
      type="line"
      height="350"
      :options="lineChartOptions"
      :series="lineSeries"
    />
  </div>
</template>

<script>
import axios from '../services/axios';

export default {
  data() {
    return {
      selectedYear: new Date().getFullYear(), // Default to current year
      years: [], // Years will be populated from the backend
      totalAmount: 0, // To hold total amount for the selected year
      // Bar chart options
      barChartOptions: {
        chart: {
          id: 'bar-chart',
        },
        xaxis: {
          categories: [
            'January', 'February', 'March', 'April', 
            'May', 'June', 'July', 'August', 
            'September', 'October', 'November', 'December'
          ],
        },
        title: {
          text: 'Sales Bar Chart',
        },
      },
      // Data for the bar chart
      barSeries: [
        {
          name: 'Sales',
          data: [],
        },
      ],

      // Line chart options
      lineChartOptions: {
        chart: {
          id: 'line-chart',
          toolbar: {
            show: true,
          },
        },
        xaxis: {
          categories: [
            'January', 'February', 'March', 'April', 
            'May', 'June', 'July', 'August', 
            'September', 'October', 'November', 'December'
          ],
        },
        title: {
          text: 'Sales Line Chart',
        },
      },
      // Data for the line chart
      lineSeries: [
        {
          name: 'Sales',
          data: [],
        },
      ],
    };
  },
  created() {
    this.fetchYears(); // Fetch years when the component is created
    this.fetchMonthlyData(); // Fetch data for the default year
  },
  methods: {
    async fetchYears() {
      try {
        const response = await axios.get('/invoices/years'); // Fetch years from the API
        this.years = response.data; // Populate the years array
      } catch (error) {
        console.error('Error fetching years:', error);
      }
    },
    async fetchMonthlyData() {
      try {
        const response = await axios.get('/invoices/monthly-data', {
          params: { year: this.selectedYear }, // Pass selected year to the API
        });
        const salesData = Object.values(response.data.monthly_data);

        // Assign data to bar and line series
        this.barSeries[0].data = salesData; // Assuming the same data for both charts
        this.lineSeries[0].data = salesData; // Update this if you have different logic

        // Set the total amount for the selected year
        this.totalAmount = response.data.total_amount;
      } catch (error) {
        console.error('Error fetching monthly data:', error);
      }
    },
  },
};
</script>

<style scoped>
/* Add any additional styles for your charts or layout here */
h2 {
  text-align: center;
}

h3 {
  margin-top: 20px;
  text-align: center;
}

.form-label {
  font-weight: bold;
  font-size: 1.1em;
}

.form-select {
  padding: 0.5rem;
  border-radius: 0.25rem;
  max-width: 20%;
}

.fa-calendar {
  color: #007bff; /* Bootstrap primary color */
}
</style>
