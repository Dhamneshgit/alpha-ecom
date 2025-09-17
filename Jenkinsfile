pipeline {
    agent any

    environment {
        KUBECONFIG = "/var/jenkins_home/.kube/config"
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build Docker Image') {
            steps {
                script {
                    sh 'docker build -t amitsonialpha/ecom-local:latest .'
                    sh 'docker push amitsonialpha/ecom-local:latest'
                }
            }
        }

        stage('Deploy to Kubernetes') {
            steps {
                script {
                    sh 'kubectl set image deployment/ecom-php ecom-alphawizz=amitsonialpha/ecom-local:latest'
                    sh 'kubectl rollout restart deployment ecom-php'
                }
            }
        }
    }
}

