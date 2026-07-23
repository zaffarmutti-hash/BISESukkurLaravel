
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js';
import { Bar } from 'react-chartjs-2';

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
);

interface ExampleChartProps {
  title?: string;
}

export function ExampleChart({ title = 'Student Enrollment' }: ExampleChartProps) {
  const data = {
    labels: ['2020', '2021', '2022', '2023', '2024', '2025'],
    datasets: [
      {
        label: 'Enrolled Students',
        data: [120, 190, 300, 500, 450, 600],
        backgroundColor: 'rgba(27, 58, 107, 0.8)',
        borderColor: 'rgba(27, 58, 107, 1)',
        borderWidth: 1,
      },
    ],
  };

  const options = {
    responsive: true,
    plugins: {
      legend: {
        position: 'top' as const,
      },
      title: {
        display: true,
        text: title,
      },
    },
  };

  return <Bar data={data} options={options} />;
}
