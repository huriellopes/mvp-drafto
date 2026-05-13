import http from 'k6/http';
import { check, sleep } from 'k6';

// Configuração do Teste
export const options = {
  stages: [
    { duration: '30s', target: 20 }, // Sobe para 20 usuários em 30 segundos
    { duration: '1m', target: 50 },  // Estabiliza em 50 usuários por 1 minuto
    { duration: '30s', target: 0 },  // Desce para 0 usuários
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'], // 95% das requisições devem ser < 500ms
    http_req_failed: ['rate<0.01'],   // Falhas devem ser menores que 1%
  },
};

const BASE_URL = 'http://localhost'; // Altere para a URL do seu ambiente Sail se necessário

export default function () {
  // 1. Acessa a Home (Geralmente pesada pelo Livewire)
  const homeRes = http.get(BASE_URL);
  check(homeRes, {
    'home status 200': (r) => r.status === 200,
  });

  sleep(1);

  // 2. Acessa a listagem de artigos
  const exploreRes = http.get(`${BASE_URL}/artigos`);
  check(exploreRes, {
    'explore status 200': (r) => r.status === 200,
  });

  sleep(2);

  // 3. Simula visualização de um post (Testa o middleware TrackPostView)
  // Nota: Você deve ajustar o slug para um post real existente no seu banco
  const postRes = http.get(`${BASE_URL}/posts/meu-primeiro-artigo`);
  check(postRes, {
    'post view status 200': (r) => r.status === 200,
  });

  sleep(3);
}
