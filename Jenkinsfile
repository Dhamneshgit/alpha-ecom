pipeline {
    agent any

    stages {
        stage('Build Docker Image') {
            steps {
                script {
                    sh 'sudo docker build -t amitsonialpha/ecom-local:latest .'
                    sh 'sudo docker push amitsonialpha/ecom-local:latest'
                }
            }
        }
        
        stage('Deploy to Kubernetes') {
            steps {
                script {
                    sh 'kubectl set image deployment/ecom-php ecom-alphawizz=amitsonialpha/ecom-local:latest --record'
                }
            }
        }
    }
}
